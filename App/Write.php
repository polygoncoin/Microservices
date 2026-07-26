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
use Microservices\App\Constant;
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
		$this->dataEncodeObj = &$this->httpObj->httpResponseObj->dataEncodeObj;
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
			sqlConfig: $sqlConfig,
			useHierarchy: $useHierarchy
		);


		if ($return !== Constant::$FALSE) {
			return $return;
		}

		// Operate as Transaction (BEGIN COMMIT else ROLLBACK on error)
		$this->operateAsTransaction = isset($sqlConfig['isTransaction'])
			? $sqlConfig['isTransaction'] : Constant::$FALSE;

		$fetchFrom = 'Master';

		// Set Server mode to execute query on - Read / Write Server
		if ($this->httpObj->httpRequestObj->customerDbObj === Constant::$NULL) {
			$this->httpObj->httpRequestObj->customerDbObj = DbCommonFunction::connectCustomerDb(
				customerData: $this->httpObj->httpRequestObj->session['customerData'],
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
				$this->httpObj->httpRequestObj->customerQueryCacheObj->queryCacheDelete(
					customerId: $this->httpObj->httpRequestObj->customerId,
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
			$writePayloadType = $this->httpObj->httpRequestObj->session['payloadType'];
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
				&& ($objCount = $this->httpObj->httpRequestObj->dataDecodeObj->count())
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
		$this->httpObj->httpRequestObj->session['requiredFieldArrCollection'] = $this->getRequired(
			sqlConfig: $writeSqlConfig,
			flag: $writeUseHierarchy,
			isFirstCall: Constant::$TRUE
		);

		$this->dataEncodeObj->startObject(
			objectKey: 'Results'
		);
		if (
			isset($this->httpObj->httpRequestObj->session['payloadType'])
			&& $this->httpObj->httpRequestObj->session['payloadType'] === 'Array'
		) {
			if (
				in_array(
					needle: $this->httpObj->httpResponseObj->outputRepresentation,
					haystack: ['XML', 'XSLT', 'HTML'],
					strict: Constant::$TRUE
				)
			) {
				$this->dataEncodeObj->startArray(
					objectKey: 'Records'
				);
			}
		}

		$indexCount = $this->httpObj->httpRequestObj->session['payloadType'] === 'Array'
			? $this->httpObj->httpRequestObj->dataDecodeObj->count() : 1;

		// Start Write operation
		$writePayloadKeyArr = [];
		for ($index = 0; $index < $indexCount; $index++) {
			$writeCurrentPayloadKeyArr = $writePayloadKeyArr;
			if ($index === 0) {
				if ($this->httpObj->httpRequestObj->session['payloadType'] === 'Array') {
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
			if ($hashJson === Constant::$NULL) {
				if ($this->operateAsTransaction) {
					$this->httpObj->httpRequestObj->customerDbObj->begin();
				}

				$output = [];
				$output['Status'] = HttpStatus::$Ok;
				if (
					CommonFunction::isEnabled(
						httpObj: $this->httpObj,
						feature: 'customer_enabled_payload_in_response'
					)
				) {
					$output[Env::$payloadKeyInResponse] = $this->httpObj->httpRequestObj->dataDecodeObj->getCompleteArray(
						keyString: implode(
							separator: ':',
							array: $writeCurrentPayloadKeyArr
						)
					);
				}

				$writeResponse = [];
				$this->writeParent(
					writeParentSqlConfig: $writeSqlConfig,
					writeParentPayloadKeyArr: $writeCurrentPayloadKeyArr,
					writeParentRequiredFieldArr: $this->httpObj->httpRequestObj->session['requiredFieldArrCollection'],
					writeParentResponse: $writeResponse,
					writeParentUseHierarchy: $writeUseHierarchy
				);

				if ($this->httpObj->httpResponseObj->httpStatus === HttpStatus::$Ok) {
					if (
						$this->operateAsTransaction
						&& ($this->httpObj->httpRequestObj->customerDbObj->beganTransaction === Constant::$TRUE)
					) {
						$this->httpObj->httpRequestObj->customerDbObj->commit();
					}
					$output['PayloadResponse'] = $writeResponse;

					if (
						$this->httpObj->httpRequestObj->isPrivateRequest
						&& $idempotentWindow
					) {
						$this->httpObj->httpRequestObj->customerCacheObj->cacheSet(
							cacheKey: $hashKey,
							cacheValue: $output,
							cacheExpire: $idempotentWindow
						);
					}
				} else { // Failure
					$output['Status'] = $this->httpObj->httpResponseObj->httpStatus;
					$output['Error'] = $writeResponse;
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
						needle: $this->httpObj->httpResponseObj->outputRepresentation,
						haystack: ['XML', 'XSLT', 'HTML'],
						strict: Constant::$TRUE
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

		if ($this->httpObj->httpRequestObj->session['payloadType'] === 'Array') {
			if (
				in_array(
					needle: $this->httpObj->httpResponseObj->outputRepresentation,
					haystack: ['XML', 'XSLT', 'HTML'],
					strict: Constant::$TRUE
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
		if ($writeParentPayloadKey !== Constant::$NULL) {
			$isObject = $this->httpObj->httpRequestObj->dataDecodeObj->dataType(
				keyString: $writeParentPayloadKey
			) === 'Object';
		}

		$indexCount = ($isObject || $isObject === Constant::$NULL)
			? 1 : $this->httpObj->httpRequestObj->dataDecodeObj->count(
				keyString: $writeParentPayloadKey
			);

		$mode = getenv(name: $this->httpObj->httpRequestObj->session['customerData']['customer_master_db_server_query_placeholder']);
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
				&& !$this->httpObj->httpRequestObj->customerDbObj->beganTransaction
			) {
				$currentResponse['Error'] = 'Transaction rolled back';
				return;
			}

			if (
				$isObject
				|| $isObject === Constant::$NULL
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
				!$this->httpObj->httpRequestObj->dataDecodeObj->isset(
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
			$this->httpObj->httpRequestObj->session['payload'] = $this->httpObj->httpRequestObj->dataDecodeObj->get(
				keyString: $writeParentCurrentPayloadKey
			);

			if (count(value: $writeParentRequiredFieldArr)) {
				$this->httpObj->httpRequestObj->session['requiredFieldArr'] = $writeParentRequiredFieldArr;
			} else {
				$this->httpObj->httpRequestObj->session['requiredFieldArr'] = [];
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
				if ($this->hookObj === Constant::$NULL) {
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
				$this->httpObj->httpRequestObj->customerDbObj->rollBack();
				return;
			}
			if ($missExecution) {
				return;
			}

			// Execute
			$this->httpObj->httpRequestObj->customerDbObj->execQuery(
				sql: $sql,
				paramArr: $paramArr
			);
			if (
				$this->operateAsTransaction
				&& !$this->httpObj->httpRequestObj->customerDbObj->beganTransaction
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
					$id = $this->httpObj->httpRequestObj->customerDbObj->lastInsertId();
				}
				$writeParentCurrentResponse[$writeParentSqlConfig['__INSERT-IDs__']] = $id;
				$this->httpObj->httpRequestObj->session['__INSERT-IDs__'][$writeParentSqlConfig['__INSERT-IDs__']] = $id;
			} else {
				$affectedRecordCount = $this->httpObj->httpRequestObj->customerDbObj->affectedRecordCount();
				$writeParentCurrentResponse['affectedRecordCount'] = $affectedRecordCount;
			}
			$this->httpObj->httpRequestObj->customerDbObj->closeCursor();

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
				if ($this->hookObj === Constant::$NULL) {
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
			$record = $this->httpObj->httpRequestObj->session['payload'];
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

			$dataExist = $this->httpObj->httpRequestObj->dataDecodeObj->isset(
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
			if ($writeChildModulePayloadKey !== Constant::$NULL) {
				$isObject = $this->httpObj->httpRequestObj->dataDecodeObj->dataType(
					keyString: $writeChildModulePayloadKey
				) === 'Object';
			}

			$indexCount = ($isObject || $isObject === Constant::$NULL)
				? 1 : $this->httpObj->httpRequestObj->dataDecodeObj->count(
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
					|| $isObject === Constant::$NULL
				) {
					$writeChildModuleCurrentPayloadKey = $writeChildModulePayloadKey;
				} else {
					$writeChildModuleCurrentPayloadKey = "{$writeChildModulePayloadKey}:{$index}";
				}

				$dataExist = $this->httpObj->httpRequestObj->dataDecodeObj->isset(
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
			if ($isValidData !== Constant::$TRUE) {
				$this->httpObj->httpResponseObj->httpStatus = HttpStatus::$BadRequest;
				$response = $errorArr;
				$return = false;
			}
		}
		return $return;
	}
}
