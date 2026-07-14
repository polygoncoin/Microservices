<?php

/**
 * Write APIs
 * php version 8.3
 *
 * @category  WriteAPI
 * @package   Microservices
 * @author    Ramesh N. Jangid (Sharma) <polygon.co.in@gmail.com>
 * @copyright © 2026 Ramesh N. Jangid (Sharma)
 * @license   MIT https://opensource.org/license/mit
 * @link      https://github.com/polygoncoin/Microservices
 * @since     Class available since Release 1.0.0
 */

namespace Microservices\App;

use Microservices\App\AppTrait;
use Microservices\App\CommonFunction;
use Microservices\App\DataRepresentation\DataEncode;
use Microservices\App\DbCommonFunction;
use Microservices\App\Env;
use Microservices\App\Hook;
use Microservices\App\Http;
use Microservices\App\HttpStatus;
use Microservices\App\Web;

/**
 * Write APIs
 * php version 8.3
 *
 * @category  WriteAPIs
 * @package   Microservices
 * @author    Ramesh N. Jangid (Sharma) <polygon.co.in@gmail.com>
 * @copyright © 2026 Ramesh N. Jangid (Sharma)
 * @license   MIT https://opensource.org/license/mit
 * @link      https://github.com/polygoncoin/Microservices
 * @since     Class available since Release 1.0.0
 */
class Write
{
	use AppTrait;

	/**
	 * Hook object
	 *
	 * @var null|Hook
	 */
	private $hookObj = null;

	/**
	 * Operate DML As Transactions
	 *
	 * @var null|Web
	 */
	private $operateAsTransaction = null;

	/**
	 * Data Encode object
	 *
	 * @var null|DataEncode
	 */
	public $dataEncodeObj = null;

	/**
	 * HTTP object
	 *
	 * @var null|Http
	 */
	private $httpObj = null;

	/**
	 * Constructor
	 *
	 * @param Http $httpObj
	 */
	public function __construct(
		Http &$httpObj
	) {
		$this->httpObj = &$httpObj;
		$this->dataEncodeObj = &$this->httpObj->responseObj->dataEncodeObj;
	}

	/**
	 * Initialize
	 *
	 * @return bool
	 */
	public function init(): bool
	{
		return true;
	}

	/**
	 * Process
	 *
	 * @return mixed
	 */
	public function process(): mixed
	{
		$return = $this->writeBasics(
			$sqlConfig,
			$useHierarchy
		);

		if ($return !== false) {
			return $return;
		}

		// Operate as Transaction (BEGIN COMMIT else ROLLBACK on error)
		$this->operateAsTransaction = isset($sqlConfig['isTransaction'])
			? $sqlConfig['isTransaction'] : false;

		$fetchFrom = 'Master';

		// Set Server mode to execute query on - Read / Write Server
		if ($this->httpObj->requestObj->customerDbObj === null) {
			$this->httpObj->requestObj->customerDbObj = DbCommonFunction::connectCustomerDb(
				customerData: $this->httpObj->requestObj->session['customerData'],
				fetchFrom: $fetchFrom
			);
		}

		$this->write(
			writeSqlConfig: $sqlConfig,
			writeUseHierarchy: $useHierarchy
		);

		if (isset($sqlConfig['affectedQueryCacheKeyArr'])) {
			$indexCount = count(
				value: $sqlConfig['affectedQueryCacheKeyArr']
			);
			for ($index = 0; $index < $indexCount; $index++) {
				$this->httpObj->requestObj->customerQueryCacheObj->queryCacheDelete(
					customerId: $this->httpObj->requestObj->customerId,
					queryCacheKey: $sqlConfig['affectedQueryCacheKeyArr'][$index]
				);
			}
		}

		return true;
	}

	/**
	 * Perform write operation
	 *
	 * @param array $writeSqlConfig    Sql config
	 * @param bool  $writeUseHierarchy If true - Uses parent payload/results in child
	 *
	 * @return void
	 * @throws \Exception
	 */
	private function write(
		&$writeSqlConfig,
		$writeUseHierarchy
	): void {
		// Check for payloadType
		if (isset($writeSqlConfig['__PAYLOAD-TYPE__'])) {
			$writePayloadType = $this->httpObj->requestObj->session['payloadType'];
			if ($writePayloadType !== $writeSqlConfig['__PAYLOAD-TYPE__']) {
				throw new \Exception(
					message: 'Invalid payload type',
					code: HttpStatus::$BadRequest
				);
			}

			// Check for maximum object's supported when payloadType is Array
			if (
				$writeSqlConfig['__PAYLOAD-TYPE__'] === 'Array'
				&& isset($writeSqlConfig['__MAX-PAYLOAD-OBJECTS__'])
				&& ($objCount = $this->httpObj->requestObj->dataDecodeObj->count())
				&& ($objCount > $writeSqlConfig['__MAX-PAYLOAD-OBJECTS__'])
			) {
				throw new \Exception(
					message: 'Maximum supported payload count is '
						. $writeSqlConfig['__MAX-PAYLOAD-OBJECTS__'],
					code: HttpStatus::$BadRequest
				);
			}
		}

		// Set required fields
		$this->httpObj->requestObj->session['requiredFieldArrCollection'] = $this->getRequired(
			sqlConfig: $writeSqlConfig,
			flag: $writeUseHierarchy,
			isFirstCall: true
		);

		$this->dataEncodeObj->startObject(
			objectKey: 'Results'
		);
		if (
			isset($this->httpObj->requestObj->session['payloadType'])
			&& $this->httpObj->requestObj->session['payloadType'] === 'Array'
		) {
			if (
				in_array(
					needle: $this->httpObj->responseObj->outputRepresentation,
					haystack: ['XML', 'XSLT', 'HTML'],
					strict: true
				)
			) {
				$this->dataEncodeObj->startArray(
					objectKey: 'Records'
				);
			}
		}

		// Perform action
		$indexCount = $this->httpObj->requestObj->session['payloadType'] === 'Array'
			? $this->httpObj->requestObj->dataDecodeObj->count() : 1;

		$writePayloadKeyArr = [];
		for ($index = 0; $index < $indexCount; $index++) {
			$writeCurrentPayloadKeyArr = $writePayloadKeyArr;
			if ($index === 0) {
				if ($this->httpObj->requestObj->session['payloadType'] === 'Array') {
					$writeCurrentPayloadKeyArr[] = "{$index}";
				} else {
					$writeCurrentPayloadKeyArr[] = '';
				}
			} else {
				$writeCurrentPayloadKeyArr[] = "{$index}";
			}

			// Check for Idempotent Window
			[$idempotentWindow, $hashKey, $hashJson] = $this->checkIdempotent(
				sqlConfig: $writeSqlConfig,
				payloadArr: $writeCurrentPayloadKeyArr
			);

			// Begin DML operation
			if ($hashJson === null) {
				if ($this->operateAsTransaction) {
					$this->httpObj->requestObj->customerDbObj->begin();
				}
				
				$output = [];
				$output['Status'] = HttpStatus::$Ok;
				if (
					CommonFunction::isEnabled(
						httpObj: $this->httpObj,
						feature: 'customer_enabled_payload_incurrentResponse'
					)
				) {
					$output[Env::$payloadKeyInResponse] = $this->httpObj->requestObj->dataDecodeObj->getCompleteArray(
						keyString: implode(
							separator: ':',
							array: $writeCurrentPayloadKeyArr
						)
					);
				}

				$response = [];
				$this->writeParent(
					writeParentSqlConfig: $writeSqlConfig,
					writeParentPayloadKeyArr: $writeCurrentPayloadKeyArr,
					writeParentRequiredFieldArr: $this->httpObj->requestObj->session['requiredFieldArrCollection'],
					writeParentResponse: $writeResponse,
					writeParentUseHierarchy: $writeUseHierarchy
				);

				if ($this->httpObj->responseObj->httpStatus === HttpStatus::$Ok) {
					if (
						$this->operateAsTransaction
						&& ($this->httpObj->requestObj->customerDbObj->beganTransaction === true)
					) {
						$this->httpObj->requestObj->customerDbObj->commit();
					}
					$output['PayloadResponse'] = $response;

					if ($idempotentWindow) {
						$this->httpObj->requestObj->customerCacheObj->cacheSet(
							cacheKey: $hashKey,
							cacheValue: $output,
							cacheExpire: $idempotentWindow
						);
					}
				} else { // Failure
					$output['Status'] = $this->httpObj->responseObj->httpStatus;
					$output['Error'] = $response;
				}
			} else {
				$output = CommonFunction::jsonDecode(
					value: $hashJson
				);
			}

			if ($writeCurrentPayloadKeyArr[0] === '') {
				foreach ($output as $outputKey => &$outputKeyValue) {
					$this->dataEncodeObj->addKeyData(
						objectKey: $outputKey,
						data: $outputKeyValue
					);
				}
			} else {
				if (
					in_array(
						needle: $this->httpObj->responseObj->outputRepresentation,
						haystack: ['XML', 'XSLT', 'HTML'],
						strict: true
					)
				) {
					$this->dataEncodeObj->startObject(
						objectKey: 'Record'
					);
					foreach ($output as $outputKey => &$outputKeyValue) {
						$this->dataEncodeObj->addKeyData(
							objectKey: $outputKey,
							data: $outputKeyValue
						);
					}
					$this->dataEncodeObj->endObject();
				} else {
					$this->dataEncodeObj->addKeyData(
						objectKey: $index,
						data: $output
					);
				}
			}
		}

		if ($this->httpObj->requestObj->session['payloadType'] === 'Array') {
			if (
				in_array(
					needle: $this->httpObj->responseObj->outputRepresentation,
					haystack: ['XML', 'XSLT', 'HTML'],
					strict: true
				)
			) {
				$this->dataEncodeObj->endArray();
			}
		}
		$this->dataEncodeObj->endObject();
	}

	/**
	 * Write Parent Function
	 *
	 * @param array $writeParentSqlConfig        Sql config
	 * @param array $writeParentPayloadKeyArr       Payload Indexes
	 * @param array $writeParentRequiredFieldArr Required fields
	 * @param array $writeParentResponse         Response by reference
	 * @param bool  $writeParentUseHierarchy     If true - Uses parent payload/results in child
	 *
	 * @return void
	 * @throws \Exception
	 */
	private function writeParent(
		&$writeParentSqlConfig,
		&$writeParentPayloadKeyArr,
		&$writeParentRequiredFieldArr,
		&$writeParentResponse,
		$writeParentUseHierarchy
	): void {
		$writeParentPayloadKey = is_array(
			value: $writeParentPayloadKeyArr
		) ? trim(
			string: implode(
				separator: ':',
				array: $writeParentPayloadKeyArr
			),
			characters: ':'
		) : null;

		$isObject = null;
		if ($writeParentPayloadKey !== null) {
			$isObject = $this->httpObj->requestObj->dataDecodeObj->dataType(
				keyString: $writeParentPayloadKey
			) === 'Object';
		}

		$indexCount = ($isObject || $isObject === null)
			? 1 : $this->httpObj->requestObj->dataDecodeObj->count(
				keyString: $writeParentPayloadKey
			);

		$mode = getenv(name: $this->httpObj->requestObj->session['customerData']['customer_master_db_server_query_placeholder']);
		$function = "getSqlAndParam{$mode}Mode";

		for ($index = 0; $index < $indexCount; $index++) {
			if (
				$isObject
				&& $index > 0
			) {
				return;
			}

			if (
				$this->operateAsTransaction
				&& !$this->httpObj->requestObj->customerDbObj->beganTransaction
			) {
				$currentResponse['Error'] = 'Transaction rolled back';
				return;
			}

			if (
				$isObject
				|| $isObject === null
			) {
				$writeParentCurrentResponse = &$writeParentResponse;
			} else {
				$writeParentResponse[$index] = [];
				$writeParentCurrentResponse = &$writeParentResponse[$index];
			}
			$writeParentCurrentPayloadKeyArr = $writeParentPayloadKeyArr;

			if (
				!$isObject
				&& !$writeParentUseHierarchy
			) {
				array_push(
					$writeParentCurrentPayloadKeyArr,
					$index
				);
			}

			$writeParentCurrentPayloadKey = is_array(
				value: $writeParentCurrentPayloadKeyArr
			) ? implode(
				separator: ':',
				array: $writeParentCurrentPayloadKeyArr
			) : '';

			if (
				!$this->httpObj->requestObj->dataDecodeObj->isset(
					keyString: $writeParentCurrentPayloadKey
				)
			) {
				if ($writeParentUseHierarchy) {
					throw new \Exception(
						message: "Payload key '{$writeParentCurrentPayloadKey}' not set",
						code: HttpStatus::$NotFound
					);
				} else {
					continue;
				}
			}

			// Load Payload
			$this->httpObj->requestObj->session['payload'] = $this->httpObj->requestObj->dataDecodeObj->get(
				keyString: $writeParentCurrentPayloadKey
			);

			if (count(value: $writeParentRequiredFieldArr)) {
				$this->httpObj->requestObj->session['requiredFieldArr'] = $writeParentRequiredFieldArr;
			} else {
				$this->httpObj->requestObj->session['requiredFieldArr'] = [];
			}

			if (
				Env::$enableGlobalCounter
				&& isset($writeParentSqlConfig['__VARIABLES__']['__GLOBAL_COUNTER__'])
			) {
				$writeParentSqlConfig['__VARIABLES__']['__GLOBAL_COUNTER__'] = Counter::getGlobalCounter();
			}

			// Validation
			if (
				isset($writeParentSqlConfig['__VALIDATE__'])
				&& !$this->isValidPayload(
					sqlConfig: $writeParentSqlConfig,
					response: $writeParentCurrentResponse
				)
			) {
				continue;
			}

			// Execute - Pre Hook
			if (isset($writeParentSqlConfig['__PRE-SQL-HOOKS__'])) {
				if ($this->hookObj === null) {
					$this->hookObj = new Hook(
						httpObj: $this->httpObj
					);
				}
				$this->hookObj->triggerHook(
					hookArr: $writeParentSqlConfig['__PRE-SQL-HOOKS__']
				);
			}

			// Set SQL and ParamArr
			[$id, $sql, $paramArr, $errorArr, $missExecution] = $this->$function(
				sqlConfig: $writeParentSqlConfig
			);
			if (!empty($errorArr)) {
				$writeParentCurrentResponse['Error'] = $errorArr;
				$this->httpObj->requestObj->customerDbObj->rollBack();
				return;
			}
			if ($missExecution) {
				return;
			}

			// Execute
			$this->httpObj->requestObj->customerDbObj->execQuery(
				sql: $sql,
				paramArr: $paramArr
			);
			if (
				$this->operateAsTransaction
				&& !$this->httpObj->requestObj->customerDbObj->beganTransaction
			) {
				$writeParentCurrentResponse['Error'] = 'Something went wrong';
				return;
			}
			if (isset($writeParentSqlConfig['__INSERT-IDs__'])) {
				if (
					Env::$enableGlobalCounter
					&& isset($writeParentSqlConfig['__VARIABLES__']['__GLOBAL_COUNTER__'])
				) {
					$id = $writeParentSqlConfig['__VARIABLES__']['__GLOBAL_COUNTER__'];
				} else {
					$id = $this->httpObj->requestObj->customerDbObj->lastInsertId();
				}
				$writeParentCurrentResponse[$writeParentSqlConfig['__INSERT-IDs__']] = $id;
				$this->httpObj->requestObj->session['__INSERT-IDs__'][$writeParentSqlConfig['__INSERT-IDs__']] = $id;
			} else {
				$affectedRecordCount = $this->httpObj->requestObj->customerDbObj->affectedRecordCount();
				$writeParentCurrentResponse['affectedRecordCount'] = $affectedRecordCount;
			}
			$this->httpObj->requestObj->customerDbObj->closeCursor();

			// Triggers
			if (isset($writeParentSqlConfig['__TRIGGERS__'])) {
				$this->dataEncodeObj->addKeyData(
					objectKey: '__TRIGGERS__',
					data: $this->getTriggerData(
						triggerConfig: $writeParentSqlConfig['__TRIGGERS__']
					)
				);
			}

			// Execute - Post Hook
			if (isset($writeParentSqlConfig['__POST-SQL-HOOKS__'])) {
				if ($this->hookObj === null) {
					$this->hookObj = new Hook(
						httpObj: $this->httpObj
					);
				}
				$this->hookObj->triggerHook(
					hookArr: $writeParentSqlConfig['__POST-SQL-HOOKS__']
				);
			}

			// Call Child
			if (isset($writeParentSqlConfig['__SUB-QUERY__'])) {
				$this->writeChild(
					writeChildSqlConfig: $writeParentSqlConfig,
					writeChildPayloadKeyArr: $writeParentCurrentPayloadKeyArr,
					writeChildRequiredFieldArr: $writeParentRequiredFieldArr,
					writeChildResponse: $writeParentCurrentResponse,
					writeChildUseHierarchy: $writeParentUseHierarchy
				);
			}
		}
	}

	/**
	 * Write Child Function
	 *
	 * @param array $writeChildSqlConfig        Sql config
	 * @param array $writeChildPayloadKeyArr    Payload Key's
	 * @param array $writeChildRequiredFieldArr Required fields
	 * @param array $writeChildResponse         Response by reference
	 * @param bool  $writeChildUseHierarchy     If true - Uses parent payload/results in child
	 *
	 * @return void
	 */
	private function writeChild(
		&$writeChildSqlConfig,
		&$writeChildPayloadKeyArr,
		&$writeChildRequiredFieldArr,
		&$writeChildResponse,
		$writeChildUseHierarchy
	): void {
		if ($writeChildUseHierarchy) {
			$record = $this->httpObj->requestObj->session['payload'];
			$this->resetFetchData(
				fetchFrom: 'sqlPayload',
				payloadKeyArr: $writeChildPayloadKeyArr,
				record: $record
			);
		}

		if (
			isset($writeChildPayloadKeyArr[0])
			&& $writeChildPayloadKeyArr[0] === ''
		) {
			$writeChildPayloadKeyArr = array_shift(
				$writeChildPayloadKeyArr
			);
		}
		if (!is_array(value: $writeChildPayloadKeyArr)) {
			$writeChildPayloadKeyArr = [];
		}

		if (
			!(
				isset($writeChildSqlConfig['__SUB-QUERY__'])
				&& !$this->isObject(
					arr: $writeChildSqlConfig['__SUB-QUERY__']
				)
			)
		) {
			return;
		}

		foreach ($writeChildSqlConfig['__SUB-QUERY__'] as $writeModule => &$writeChildModuleSqlConfig) {
			$dataExist = false;

			$writeChildResponse[$writeModule] = [];
			$writeChildModuleResponse = &$writeChildResponse[$writeModule];
			
			$writeChildModulePayloadKeyArr = $writeChildPayloadKeyArr;
			array_push(
				$writeChildModulePayloadKeyArr,
				$writeModule
			);

			$writeChildModulePayloadKey = is_array(
				value: $writeChildModulePayloadKeyArr
			) ? implode(
				separator: ':',
				array: $writeChildModulePayloadKeyArr
			) : null;

			$dataExist = $this->httpObj->requestObj->dataDecodeObj->isset(
				keyString: $writeChildModulePayloadKey
			);
			if (
				$writeChildUseHierarchy
				&& !$dataExist
			) { // use parent data of a payload
				throw new \Exception(
					message: "Invalid payload: Module '{$writeModule}' missing",
					code: HttpStatus::$NotFound
				);
			}
			if ($dataExist) {
				return;
			}

			$isObject = null;
			if ($writeChildModulePayloadKey !== null) {
				$isObject = $this->httpObj->requestObj->dataDecodeObj->dataType(
					keyString: $writeChildModulePayloadKey
				) === 'Object';
			}

			$indexCount = ($isObject || $isObject === null)
				? 1 : $this->httpObj->requestObj->dataDecodeObj->count(
					keyString: $writeChildModulePayloadKey
				);

			if (isset($writeChildRequiredFieldArr[$writeModule])) {
				$writeChildModuleRequiredFieldArr = &$writeChildRequiredFieldArr[$writeModule];
			} else {
				$writeChildModuleRequiredFieldArr = &$writeChildRequiredFieldArr;
			}

			$writeChildModuleUseHierarchy = $writeChildUseHierarchy ?? $this->getUseHierarchy(
				sqlConfig: $writeChildModuleSqlConfig,
				keyword: 'useHierarchy'
			);

			for ($index = 0; $index < $indexCount; $index++) {
				$writeChildModuleCurrentPayloadKeyArr = $writeChildModulePayloadKeyArr;
				array_push(
					$writeChildModuleCurrentPayloadKeyArr,
					$writeModule
				);

				$writeChildModuleCurrentResponse = &$writeChildModuleResponse;
				$writeChildModuleCurrentResponse[$index] = [];
				$writeChildModuleCurrentResponse = &$writeChildCurrentResponse[$index];

				if (
					$isObject
					|| $isObject === null
				) {
					$writeChildModuleCurrentPayloadKey = $writeChildModulePayloadKey;
				} else {
					$writeChildModuleCurrentPayloadKey = "{$writeChildModulePayloadKey}:{$index}";
				}

				$dataExist = $this->httpObj->requestObj->dataDecodeObj->isset(
					keyString: $writeChildModuleCurrentPayloadKey
				);

				if (
					$writeChildModuleUseHierarchy
					&& !$dataExist
				) { // use parent data of a payload
					throw new \Exception(
						message: "Invalid payload: Module '{$writeModule}' missing",
						code: HttpStatus::$NotFound
					);
				}

				if (!$dataExist) {
					continue;
				}

				$this->writeParent(
					writeParentSqlConfig: $writeChildModuleSqlConfig,
					writeParentPayloadKeyArr: $writeChildModuleCurrentPayloadKeyArr,
					writeParentRequiredFieldArr: $writeChildModuleRequiredFieldArr,
					writeParentResponse: $writeChildModuleCurrentResponse,
					writeParentUseHierarchy: $writeChildModuleUseHierarchy
				);
			}
		}
	}

	/**
	 * Explain write configuration
	 *
	 * @param array $sqlConfig    Sql config
	 * @param bool  $useHierarchy If true - Uses parent payload/results in child
	 *
	 * @return bool
	 */
	private function explain(
		&$sqlConfig,
		$useHierarchy
	): bool {
		$this->dataEncodeObj->startObject(
			objectKey: 'Config'
		);
		$this->dataEncodeObj->addKeyData(
			objectKey: 'Route',
			data: $this->httpObj->requestObj->routeParserObj->configuredRoute
		);
		if (
			CommonFunction::isEnabled(
				httpObj: $this->httpObj,
				feature: 'customer_enabled_payload_incurrentResponse'
			)
		) {
			$this->dataEncodeObj->addKeyData(
				objectKey: Env::$payloadKeyInResponse,
				data: $this->getExplainParam(
					sqlConfig: $sqlConfig,
					flag: $useHierarchy,
					isFirstCall: true
				)
			);
		}
		$this->dataEncodeObj->endObject();

		return true;
	}

	/**
	 * Validate payload
	 *
	 * @param array $sqlConfig Sql config
	 * @param array $response  Response by reference
	 *
	 * @return bool
	 */
	private function isValidPayload(
		$sqlConfig,
		&$response
	): bool {
		$return = true;
		$isValidData = true;
		if (isset($sqlConfig['__VALIDATE__'])) {
			[$isValidData, $errorArr] = $this->validate(
				validationConfig: $sqlConfig['__VALIDATE__']
			);
			if ($isValidData !== true) {
				$this->httpObj->responseObj->httpStatus = HttpStatus::$BadRequest;
				$response = $errorArr;
				$return = false;
			}
		}
		return $return;
	}
}
