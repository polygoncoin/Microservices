<?php

/**
 * Supplement APIs
 * php version 8.3
 *
 * @category  Supplement
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
 * Supplement APIs
 * php version 8.3
 *
 * @category  Supplement
 * @package   Microservices
 * @author    Ramesh N. Jangid (Sharma) <polygon.co.in@gmail.com>
 * @copyright © 2026 Ramesh N. Jangid (Sharma)
 * @license   MIT https://opensource.org/license/mit
 * @link      https://github.com/polygoncoin/Microservices
 * @since     Class available since Release 1.0.0
 */
class Supplement
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
	 * @var null|bool
	 */
	private $operateAsTransaction = null;

	/**
	 * Data Encode object
	 *
	 * @var null|DataEncode
	 */
	public $dataEncodeObj = null;

	/**
	 * Supplement Class object
	 *
	 * @var null|object
	 */
	public $supplementObj = null;

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
	 * @param string $supplementClass Supplement class
	 *
	 * @return bool
	 */
	public function init(
		&$supplementClass
	): bool {
		$this->supplementObj = new $supplementClass(
			$this->httpObj
		);
		return $this->supplementObj->init();
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

		$fetchFrom = $sqlConfig['fetchFrom'] ?? 'Master';

		// Set Server mode to execute query on - Read / Write Server
		if ($this->httpObj->requestObj->customerDbObj === null) {
			$this->httpObj->requestObj->customerDbObj = DbCommonFunction::connectCustomerDb(
				customerData: $this->httpObj->requestObj->session['customerData'],
				fetchFrom: $fetchFrom
			);
		}

		$this->supplement(
			supplementSqlConfig: $sqlConfig,
			supplementUseHierarchy: $useHierarchy
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
	 * Process Function to insert/update
	 *
	 * @param array $supplementSqlConfig    Sql config
	 * @param bool  $supplementUseHierarchy If true - Uses parent payload/results in child
	 *
	 * @return void
	 * @throws \Exception
	 */
	private function supplement(
		&$supplementSqlConfig,
		$supplementUseHierarchy
	): void {
		// Check for payloadType
		if (isset($supplementSqlConfig['__PAYLOAD-TYPE__'])) {
			$supplementPayloadType = $this->httpObj->requestObj->session['payloadType'];
			if ($supplementPayloadType !== $supplementSqlConfig['__PAYLOAD-TYPE__']) {
				throw new \Exception(
					message: 'Invalid payload type',
					code: HttpStatus::$BadRequest
				);
			}

			// Check for maximum object's supported when payloadType is Array
			if (
				$supplementSqlConfig['__PAYLOAD-TYPE__'] === 'Array'
				&& isset($supplementSqlConfig['__MAX-PAYLOAD-OBJECTS__'])
				&& ($objCount = $this->httpObj->requestObj->dataDecodeObj->count())
				&& ($objCount > $supplementSqlConfig['__MAX-PAYLOAD-OBJECTS__'])
			) {
				throw new \Exception(
					message: 'Maximum supported payload count is '
						. $supplementSqlConfig['__MAX-PAYLOAD-OBJECTS__'],
					code: HttpStatus::$BadRequest
				);
			}
		}

		// Set required fields
		$this->httpObj->requestObj->session['requiredFieldArrCollection'] = $this->getRequired(
			sqlConfig: $supplementSqlConfig,
			flag: $supplementUseHierarchy,
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
			$supplementCurrentPayloadKeyArr = $writePayloadKeyArr;
			if ($index === 0) {
				if ($this->httpObj->requestObj->session['payloadType'] === 'Array') {
					$supplementCurrentPayloadKeyArr[] = "{$index}";
				} else {
					$supplementCurrentPayloadKeyArr[] = '';
				}
			} else {
				$supplementCurrentPayloadKeyArr[] = "{$index}";
			}

			// Check for Idempotent Window
			[$idempotentWindow, $hashKey, $hashJson] = $this->checkIdempotent(
				sqlConfig: $supplementSqlConfig,
				payloadArr: $supplementCurrentPayloadKeyArr
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
						feature: 'customer_enabled_payload_in_response'
					)
				) {
					$output[Env::$payloadKeyInResponse] = $this->httpObj->requestObj->dataDecodeObj->getCompleteArray(
						keyString: implode(
							separator: ':',
							array: $supplementCurrentPayloadKeyArr
						)
					);
				}

				$supplementResponse = [];
				$this->supplementParent(
					supplementParentSqlConfig: $supplementSqlConfig,
					supplementParentPayloadKeyArr: $supplementCurrentPayloadKeyArr,
					supplementParentRequiredFieldArr: $this->httpObj->requestObj->session['requiredFieldArrCollection'],
					supplementParentResponse: $supplementResponse,
					supplementParentModule: '',
					supplementParentUseHierarchy: $supplementUseHierarchy
				);

				if ($this->httpObj->responseObj->httpStatus === HttpStatus::$Ok) {
					if (
						$this->operateAsTransaction
						&& ($this->httpObj->requestObj->customerDbObj->beganTransaction === true)
					) {
						$this->httpObj->requestObj->customerDbObj->commit();
					}
					$output['PayloadResponse'] = $supplementResponse;

					if ($idempotentWindow) {
						$this->httpObj->requestObj->customerCacheObj->cacheSet(
							cacheKey: $hashKey,
							cacheValue: $output,
							cacheExpire: $idempotentWindow
						);
					}
				} else { // Failure
					$output['Status'] = $this->httpObj->responseObj->httpStatus;
					$output['Error'] = $writeResponse;
				}
			} else {
				$output = CommonFunction::jsonDecode(
					value: $hashJson
				);
			}

			if ($supplementCurrentPayloadKeyArr[0] === '') {
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
	 * Supplement Parent Function
	 *
	 * @param array  $supplementParentSqlConfig        Sql config
	 * @param array  $supplementParentPayloadKeyArr       Payload Indexes
	 * @param array  $supplementParentRequiredFieldArr Required fields
	 * @param array  $supplementParentResponse         Response by reference
	 * @param string $supplementParentModule           Parent Module
	 * @param bool   $supplementParentUseHierarchy     If true - Uses parent payload/results in child
	 *
	 * @return void
	 * @throws \Exception
	 */
	private function supplementParent(
		&$supplementParentSqlConfig,
		&$supplementParentPayloadKeyArr,
		&$supplementParentRequiredFieldArr,
		&$supplementParentResponse,
		$supplementParentModule,
		$supplementParentUseHierarchy
	): void {
		$supplementParentPayloadKey = is_array(
			value: $supplementParentPayloadKeyArr
		) ? trim(
			string: implode(
				separator: ':',
				array: $supplementParentPayloadKeyArr
			),
			characters: ':'
		) : null;

		$isObject = null;
		if ($supplementParentPayloadKey !== null) {
			$isObject = $this->httpObj->requestObj->dataDecodeObj->dataType(
				keyString: $supplementParentPayloadKey
			) === 'Object';
		}

		$indexCount = ($isObject || $isObject === null)
			? 1 : $this->httpObj->requestObj->dataDecodeObj->count(
				keyString: $supplementParentPayloadKey
			);

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
				$supplementParentCurrentResponse = &$supplementParentResponse;
			} else {
				$supplementParentResponse[$index] = [];
				$supplementParentCurrentResponse = &$supplementParentResponse[$index];
			}

			$supplementParentCurrentPayloadKeyArr = $supplementParentPayloadKeyArr;

			if (
				!$isObject
				&& !$supplementParentUseHierarchy
			) {
				array_push(
					$supplementParentCurrentPayloadKeyArr,
					$index
				);
			}

			$supplementParentCurrentPayloadKey = is_array(
				value: $supplementParentCurrentPayloadKeyArr
			) ? implode(
				separator: ':',
				array: $supplementParentCurrentPayloadKeyArr
			) : '';

			if (
				!$this->httpObj->requestObj->dataDecodeObj->isset(
					keyString: $supplementParentCurrentPayloadKey
				)
			) {
				if ($supplementParentUseHierarchy) {
					throw new \Exception(
						message: "Payload key '{$supplementParentCurrentPayloadKey}' not set",
						code: HttpStatus::$NotFound
					);
				} else {
					continue;
				}
			}

			// Load Payload
			$this->httpObj->requestObj->session['payload'] = $this->httpObj->requestObj->dataDecodeObj->get(
				keyString: $supplementParentCurrentPayloadKey
			);

			if (count(value: $supplementParentRequiredFieldArr)) {
				$this->httpObj->requestObj->session['requiredFieldArr'] = $supplementParentRequiredFieldArr;
			} else {
				$this->httpObj->requestObj->session['requiredFieldArr'] = [];
			}

			// Validation
			if (
				isset($supplementParentSqlConfig['__VALIDATE__'])
				&& !$this->isValidPayload(
					sqlConfig: $supplementParentSqlConfig,
					response: $supplementParentCurrentResponse
				)
			) {
				continue;
			}

			// Execute - Pre Hook
			if (isset($supplementParentSqlConfig['__PRE-SQL-HOOKS__'])) {
				if ($this->hookObj === null) {
					$this->hookObj = new Hook(
						httpObj: $this->httpObj
					);
				}
				$this->hookObj->triggerHook(
					hookArr: $supplementParentSqlConfig['__PRE-SQL-HOOKS__']
				);
			}

			// Set Function
			if ($module === '') {
				$processFunction  = 'process';
			} else {
				$processFunction  = "{$module}Process";
			}

			// Execute
			$supplementParentCurrentResponse = $this->supplementObj->$processFunction();
			if (
				$this->operateAsTransaction
				&& !$this->httpObj->requestObj->customerDbObj->beganTransaction
			) {
				$supplementParentCurrentResponse['Error'] = 'Something went wrong';
				return;
			} else {
				
			}

			// Triggers
			if (isset($supplementParentSqlConfig['__TRIGGERS__'])) {
				$this->dataEncodeObj->addKeyData(
					objectKey: '__TRIGGERS__',
					data: $this->getTriggerData(
						triggerConfig: $supplementParentSqlConfig['__TRIGGERS__']
					)
				);
			}

			// Execute - Post Hook
			if (isset($supplementParentSqlConfig['__POST-SQL-HOOKS__'])) {
				if ($this->hookObj === null) {
					$this->hookObj = new Hook(
						httpObj: $this->httpObj
					);
				}
				$this->hookObj->triggerHook(
					hookArr: $supplementParentSqlConfig['__POST-SQL-HOOKS__']
				);
			}

			// Call Child
			if (isset($supplementParentSqlConfig['__SUB-QUERY__'])) {
				$this->supplementChild(
					supplementChildSqlConfig: $supplementParentSqlConfig,
					supplementChildPayloadKeyArr: $supplementParentCurrentPayloadKeyArr,
					supplementChildRequiredFieldArr: $supplementParentRequiredFieldArr,
					supplementChildResponse: $supplementParentCurrentResponse,
					supplementChildUseHierarchy: $supplementParentUseHierarchy
				);
			}
		}
	}

	/**
	 * Write Child Function
	 *
	 * @param array $supplementChildSqlConfig        Sql config
	 * @param array $supplementChildPayloadKeyArr       Payload Indexes
	 * @param array $supplementChildRequiredFieldArr Required fields
	 * @param array $supplementChildResponse         Response by reference
	 * @param bool  $supplementChildUseHierarchy     If true - Uses parent payload/results in child
	 *
	 * @return void
	 */
	private function supplementChild(
		&$supplementChildSqlConfig,
		&$supplementChildPayloadKeyArr,
		&$supplementChildRequiredFieldArr,
		&$supplementChildResponse,
		$supplementChildUseHierarchy
	): void {
		if ($supplementChildUseHierarchy) {
			$record = $this->httpObj->requestObj->session['payload'];
			$this->resetFetchData(
				fetchFrom: 'sqlPayload',
				payloadKeyArr: $supplementChildPayloadKeyArr,
				record: $record
			);
		}

		if (
			isset($supplementChildPayloadKeyArr[0])
			&& $supplementChildPayloadKeyArr[0] === ''
		) {
			$supplementChildPayloadKeyArr = array_shift(
				$supplementChildPayloadKeyArr
			);
		}
		if (!is_array(value: $supplementChildPayloadKeyArr)) {
			$supplementChildPayloadKeyArr = [];
		}

		if (
			!(
				isset($supplementChildSqlConfig['__SUB-QUERY__'])
				&& !$this->isObject(
					arr: $supplementChildSqlConfig['__SUB-QUERY__']
				)
			)
		) {
			return;
		}

		foreach ($supplementChildSqlConfig['__SUB-PAYLOAD__'] as $supplementModule => &$supplementChildModuleSqlConfig) {
			$dataExist = false;

			$supplementChildResponse[$supplementModule] = [];
			$supplementChildModuleResponse = &$supplementChildResponse[$supplementModule];

			$supplementChildModulePayloadKeyArr = $supplementChildPayloadKeyArr;
			array_push(
				$supplementChildModulePayloadKeyArr,
				$supplementModule
			);

			$supplementChildModulePayloadKey = is_array(
				value: $supplementChildModulePayloadKeyArr
			) ? implode(
				separator: ':',
				array: $supplementChildModulePayloadKeyArr
			) : null;

			$dataExist = $this->httpObj->requestObj->dataDecodeObj->isset(
				keyString: $supplementChildModulePayloadKey
			);
			if (
				$supplementChildUseHierarchy
				&& !$dataExist
			) { // use parent data of a payload
				throw new \Exception(
					message: "Invalid payload: Module '{$supplementModule}' missing",
					code: HttpStatus::$NotFound
				);
			}
			if ($dataExist) {
				return;
			}

			$isObject = null;
			if ($supplementChildModulePayloadKey !== null) {
				$isObject = $this->httpObj->requestObj->dataDecodeObj->dataType(
					keyString: $supplementChildModulePayloadKey
				) === 'Object';
			}

			$indexCount = ($isObject || $isObject === null)
				? 1 : $this->httpObj->requestObj->dataDecodeObj->count(
					keyString: $supplementChildModulePayloadKey
				);

			if (isset($supplementChildRequiredFieldArr[$supplementModule])) {
				$supplementChildModuleRequiredFieldArr = &$supplementChildRequiredFieldArr[$supplementModule];
			} else {
				$supplementChildModuleRequiredFieldArr = &$supplementChildRequiredFieldArr;
			}

			$supplementChildModuleUseHierarchy = $supplementChildUseHierarchy ?? $this->getUseHierarchy(
				sqlConfig: $supplementChildModuleSqlConfig,
				keyword: 'useHierarchy'
			);

			for ($index = 0; $index < $indexCount; $index++) {
				$supplementChildModuleCurrentPayloadKeyArr = $supplementChildModulePayloadKeyArr;
				array_push(
					$supplementChildModuleCurrentPayloadKeyArr,
					$module
				);

				$supplementChildModuleCurrentResponse = &$supplementChildModuleResponse;
				$supplementChildModuleCurrentResponse[$index] = [];
				$supplementChildModuleCurrentResponse = &$supplementChildCurrentResponse[$index];

				if (
					$isObject
					|| $isObject === null
				) {
					$supplementChildModuleCurrentPayloadKey = $supplementChildModulePayloadKey;
				} else {
					$supplementChildModuleCurrentPayloadKey = "{$supplementChildModulePayloadKey}:{$index}";
				}

				$dataExist = $this->httpObj->requestObj->dataDecodeObj->isset(
					keyString: $supplementChildModuleCurrentPayloadKey
				);

				if (
					$supplementChildModuleUseHierarchy
					&& !$dataExist
				) { // use parent data of a payload
					throw new \Exception(
						message: "Invalid payload: Module '{$supplementModule}' missing",
						code: HttpStatus::$NotFound
					);
				}

				if (!$dataExist) {
					continue;
				}

				$this->supplementParent(
					supplementParentSqlConfig: $supplementChildModuleSqlConfig,
					supplementParentPayloadKeyArr: $supplementChildModuleCurrentPayloadKeyArr,
					supplementParentRequiredFieldArr: $supplementChildModuleRequiredFieldArr,
					supplementParentResponse: $supplementChildModuleCurrentResponse,
					supplementParentModule: $supplementModule,
					supplementParentUseHierarchy: $supplementChildModuleUseHierarchy
				);
			}
		}
	}

	/**
	 * Explain supplement configuration
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
		$this->dataEncodeObj->addKeyData(
			objectKey: 'Payload',
			data: $this->getExplainParam(
				sqlConfig: $sqlConfig,
				flag: $useHierarchy,
				isFirstCall: true
			)
		);
		$this->dataEncodeObj->endObject();

		return true;
	}

	/**
	 * Checks if the payload is valid
	 *
	 * @param array $sqlConfig  Sql config
	 * @param array $response   Response by reference
	 *
	 * @return bool
	 */
	private function isValidPayload(
		$sqlConfig,
		$response
	): bool {
		$return = true;
		$isValidData = true;
		if (isset($sqlConfig['__VALIDATE__'])) {
			[$isValidData, $errorArr] = $this->validate(
				validationConfig: $sqlConfig['__VALIDATE__']
			);
			if ($isValidData !== true) {
				$this->httpObj->responseObj->httpStatus = HttpStatus::$BadRequest;
				$response['Error'] = $errorArr;
				$return = false;
			}
		}
		return $return;
	}
}
