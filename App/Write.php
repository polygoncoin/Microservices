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
	private $hookObject = null;

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
	public $dataEncodeObject = null;

	/**
	 * HTTP object
	 * 
	 * @var null|Http
	 */
	private $httpObject = null;

	/**
	 * Constructor
	 * 
	 * @param Http $httpObject
	 */
	public function __construct(
		Http &$httpObject
	) {
		$this->httpObject = &$httpObject;
		$this->dataEncodeObject = &$this->httpObject->httpResponseObject->dataEncodeObject;
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

		$fetchDbMode = 'Master';

		// Set Server mode to execute query on - Read / Write Server
		if ($this->httpObject->httpRequestObject->customerDbObject === Constant::$NULL) {
			$this->httpObject->httpRequestObject->customerDbObject = DbCommonFunction::connectCustomerDb(
				customerData: $this->httpObject->httpRequestObject->activeRequestData['customerData'],
				fetchDbMode: $fetchDbMode
			);
		}

		$this->write(
			writeSqlConfig: $sqlConfig,
			writeUseHierarchy: $useHierarchy
		);

		if (isset($sqlConfig['affectedQueryCacheKeyArray'])) {
			$indexCount = count(
				value: $sqlConfig['affectedQueryCacheKeyArray']
			);
			for ($index = 0; $index < $indexCount; $index++) {
				$this->httpObject->httpRequestObject->customerQueryCacheObject->queryCacheDelete(
					customerId: $this->httpObject->httpRequestObject->customerId,
					queryCacheKey: $sqlConfig['affectedQueryCacheKeyArray'][$index]
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
			$writePayloadType = $this->httpObject->httpRequestObject->activeRequestData['payloadType'];
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
				&& ($objCount = $this->httpObject->httpRequestObject->dataDecodeObject->count())
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
		$this->httpObject->httpRequestObject->activeRequestData['requiredFieldArrayCollection'] = $this->getRequired(
			sqlConfig: $writeSqlConfig,
			flag: $writeUseHierarchy,
			isFirstCall: Constant::$TRUE
		);

		$this->dataEncodeObject->startObject(
			objectKey: 'Results'
		);

		if (
			isset($this->httpObject->httpRequestObject->activeRequestData['payloadType'])
			&& $this->httpObject->httpRequestObject->activeRequestData['payloadType'] === 'Array'
		) {
			if (
				in_array(
					needle: $this->httpObject->httpResponseObject->outputRepresentation,
					haystack: ['XML', 'XSLT', 'HTML'],
					strict: Constant::$TRUE
				)
			) {
				$this->dataEncodeObject->startArray(
					objectKey: 'Records'
				);
			}
		}

		$indexCount = $this->httpObject->httpRequestObject->activeRequestData['payloadType'] === 'Array'
			? $this->httpObject->httpRequestObject->dataDecodeObject->count() : 1;

		// Start Write operation
		for ($index = 0; $index < $indexCount; $index++) {
			$writePayloadKeyArray = null;

			if ($this->httpObject->httpRequestObject->activeRequestData['payloadType'] === 'Array') {
				$writePayloadKeyArray = [];
				$writePayloadKeyArray[] = "{$index}";
			}

			// Check for Idempotent Window
			[$idempotentWindow, $hashKey, $hashJson] = $this->checkIdempotent(
				sqlConfig: $writeSqlConfig,
				payloadKeyArray: $writePayloadKeyArray
			);

			// Begin DML operation
			if ($hashJson === Constant::$NULL) {
				if ($this->operateAsTransaction) {
					$this->httpObject->httpRequestObject->customerDbObject->begin();
				}

				$output = [];
				$output['Status'] = HttpStatus::$Ok;
				if (
					CommonFunction::isEnabled(
						httpObject: $this->httpObject,
						feature: 'customer_enabled_payload_in_response'
					)
				) {
					$output[Env::$payloadKeyInResponse] = $this->httpObject->httpRequestObject->dataDecodeObject->getCompleteArray(
						keyString: $this->getPayloadKey(
							payloadKeyArray: $writePayloadKeyArray
						)
					);
				}

				$writeResponse = [];
				$this->writeParent(
					writeParentSqlConfig: $writeSqlConfig,
					writeParentPayloadKeyArray: $writePayloadKeyArray,
					writeParentRequiredFieldArray: $this->httpObject->httpRequestObject->activeRequestData['requiredFieldArrayCollection'],
					writeParentResponse: $writeResponse,
					writeParentUseHierarchy: $writeUseHierarchy
				);

				if ($this->httpObject->httpResponseObject->httpStatus === HttpStatus::$Ok) {
					if (
						$this->operateAsTransaction
						&& ($this->httpObject->httpRequestObject->customerDbObject->beganTransaction === Constant::$TRUE)
					) {
						$this->httpObject->httpRequestObject->customerDbObject->commit();
					}
					$output['PayloadResponse'] = $writeResponse;

					if (
						$this->httpObject->httpRequestObject->isPrivateRequest
						&& $idempotentWindow
					) {
						$this->httpObject->httpRequestObject->customerCacheObject->cacheSet(
							cacheKey: $hashKey,
							cacheValue: $output,
							cacheExpire: $idempotentWindow
						);
					}
				} else { // Failure
					$output['Status'] = $this->httpObject->httpResponseObject->httpStatus;
					$output['Error'] = $writeResponse;
				}
			} else {
				$output = CommonFunction::jsonDecode(
					value: $hashJson
				);
			}

			if ($writePayloadKeyArray === Constant::$NULL) {
				foreach ($output as $outputKey => &$outputKeyValue) {
					$this->dataEncodeObject->addKeyData(
						objectKey: $outputKey,
						data: $outputKeyValue
					);
				}
			} else {
				if (
					in_array(
						needle: $this->httpObject->httpResponseObject->outputRepresentation,
						haystack: ['XML', 'XSLT', 'HTML'],
						strict: Constant::$TRUE
					)
				) {
					$this->dataEncodeObject->startObject(
						objectKey: 'Record'
					);
					foreach ($output as $outputKey => &$outputKeyValue) {
						$this->dataEncodeObject->addKeyData(
							objectKey: $outputKey,
							data: $outputKeyValue
						);
					}
					$this->dataEncodeObject->endObject();
				} else {
					$this->dataEncodeObject->addKeyData(
						objectKey: $index,
						data: $output
					);
				}
			}
		}

		if ($this->httpObject->httpRequestObject->activeRequestData['payloadType'] === 'Array') {
			if (
				in_array(
					needle: $this->httpObject->httpResponseObject->outputRepresentation,
					haystack: ['XML', 'XSLT', 'HTML'],
					strict: Constant::$TRUE
				)
			) {
				$this->dataEncodeObject->endArray();
			}
		}
		$this->dataEncodeObject->endObject();
	}

	/**
	 * Write Parent Function
	 * 
	 * @param array $writeParentSqlConfig          Sql config
	 * @param array $writeParentPayloadKeyArray    Payload Indexes
	 * @param array $writeParentRequiredFieldArray Required fields
	 * @param array $writeParentResponse           Response by reference
	 * @param bool  $writeParentUseHierarchy       If true - Uses parent payload/results in child
	 * 
	 * @return void
	 * @throws \Exception
	 */
	private function writeParent(
		&$writeParentSqlConfig,
		&$writeParentPayloadKeyArray,
		&$writeParentRequiredFieldArray,
		&$writeParentResponse,
		$writeParentUseHierarchy
	): void {
		if ($writeParentPayloadKeyArray === Constant::$NULL) {
			$writeParentPayloadKeyArray = [];
		}

		$writeParentPayloadKey = $this->getPayloadKey(
			payloadKeyArray: $writeParentPayloadKeyArray
		);

		$isObject = $this->httpObject->httpRequestObject->dataDecodeObject->dataType(
			keyString: $writeParentPayloadKey
		) === 'Object';

		$indexCount = ($isObject || $isObject === Constant::$NULL)
			? 1 : $this->httpObject->httpRequestObject->dataDecodeObject->count(
				keyString: $writeParentPayloadKey
			);

		$mode = getenv(name: $this->httpObject->httpRequestObject->activeRequestData['customerData']['customer_master_db_server_query_placeholder']);
		$function = "getSqlAndParam{$mode}Mode";

		for ($index = 0; $index < $indexCount; $index++) {
			if (
				$this->operateAsTransaction
				&& !$this->httpObject->httpRequestObject->customerDbObject->beganTransaction
			) {
				$currentResponse['Error'] = 'Transaction rolled back';
				return;
			}

			// For Response
			if (
				$isObject
				|| $isObject === Constant::$NULL
			) {
				$writeParentCurrentResponse = &$writeParentResponse;
			} else {
				$writeParentResponse[$index] = [];
				$writeParentCurrentResponse = &$writeParentResponse[$index];
			}

			$writeParentCurrentPayloadKeyArray = $writeParentPayloadKeyArray;
			if (!$isObject) {
				array_push(
					$writeParentCurrentPayloadKeyArray,
					$index
				);
			}

			$writeParentCurrentPayloadKey = $this->getPayloadKey(
				payloadKeyArray: $writeParentCurrentPayloadKeyArray
			);

			if (
				!$this->httpObject->httpRequestObject->dataDecodeObject->isset(
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
			$this->httpObject->httpRequestObject->activeRequestData['payload'] = $this->httpObject->httpRequestObject->dataDecodeObject->get(
				keyString: $writeParentCurrentPayloadKey
			);

			if (count(value: $writeParentRequiredFieldArray)) {
				$this->httpObject->httpRequestObject->activeRequestData['requiredFieldArray'] = $writeParentRequiredFieldArray;
			} else {
				$this->httpObject->httpRequestObject->activeRequestData['requiredFieldArray'] = [];
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
				if ($this->hookObject === Constant::$NULL) {
					$this->hookObject = new Hook(
						httpObject: $this->httpObject
					);
				}
				$this->hookObject->triggerHook(
					hookArray: $writeParentSqlConfig['__PRE-SQL-HOOKS__']
				);
			}

			// Set SQL and ParamArray
			[$id, $sql, $paramArray, $errorArray, $missExecution] = $this->$function(
				sqlConfig: $writeParentSqlConfig
			);

			if (!empty($errorArray)) {
				$writeParentCurrentResponse['Error'] = $errorArray;
				$this->httpObject->httpRequestObject->customerDbObject->rollBack();
				return;
			}
			if ($missExecution) {
				return;
			}

			// Execute
			$this->httpObject->httpRequestObject->customerDbObject->execQuery(
				sql: $sql,
				paramArray: $paramArray
			);
			if (
				$this->operateAsTransaction
				&& !$this->httpObject->httpRequestObject->customerDbObject->beganTransaction
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
					$id = $this->httpObject->httpRequestObject->customerDbObject->lastInsertId();
				}
				$writeParentCurrentResponse[$writeParentSqlConfig['__INSERT-IDs__']] = $id;
				$this->httpObject->httpRequestObject->activeRequestData['__INSERT-IDs__'][$writeParentSqlConfig['__INSERT-IDs__']] = $id;
			} else {
				$affectedRecordCount = $this->httpObject->httpRequestObject->customerDbObject->affectedRecordCount();
				$writeParentCurrentResponse['affectedRecordCount'] = $affectedRecordCount;
			}
			$this->httpObject->httpRequestObject->customerDbObject->closeCursor();

			// Triggers
			if (isset($writeParentSqlConfig['__TRIGGERS__'])) {
				$this->dataEncodeObject->addKeyData(
					objectKey: '__TRIGGERS__',
					data: $this->getTriggerData(
						triggerConfig: $writeParentSqlConfig['__TRIGGERS__']
					)
				);
			}

			// Execute - Post Hook
			if (isset($writeParentSqlConfig['__POST-SQL-HOOKS__'])) {
				if ($this->hookObject === Constant::$NULL) {
					$this->hookObject = new Hook(
						httpObject: $this->httpObject
					);
				}
				$this->hookObject->triggerHook(
					hookArray: $writeParentSqlConfig['__POST-SQL-HOOKS__']
				);
			}

			// Call Child
			if (isset($writeParentSqlConfig['__SUB-QUERY__'])) {
				$this->writeChild(
					writeChildSqlConfig: $writeParentSqlConfig,
					writeChildPayloadKeyArray: $writeParentCurrentPayloadKeyArray,
					writeChildRequiredFieldArray: $writeParentRequiredFieldArray,
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
	 * @param array $writeChildPayloadKeyArray    Payload Key's
	 * @param array $writeChildRequiredFieldArray Required fields
	 * @param array $writeChildResponse         Response by reference
	 * @param bool  $writeChildUseHierarchy     If true - Uses parent payload/results in child
	 * 
	 * @return void
	 */
	private function writeChild(
		&$writeChildSqlConfig,
		&$writeChildPayloadKeyArray,
		&$writeChildRequiredFieldArray,
		&$writeChildResponse,
		$writeChildUseHierarchy
	): void {
		if ($writeChildUseHierarchy) {
			$record = $this->httpObject->httpRequestObject->activeRequestData['payload'];
			$this->resetFetchData(
				activeRequestDataKey: 'sqlPayload',
				payloadKeyArray: $writeChildPayloadKeyArray,
				record: $record
			);
		}

		if (
			isset($writeChildPayloadKeyArray[0])
			&& $writeChildPayloadKeyArray[0] === ''
		) {
			$writeChildPayloadKeyArray = array_shift(
				$writeChildPayloadKeyArray
			);
		}
		if (!is_array(value: $writeChildPayloadKeyArray)) {
			$writeChildPayloadKeyArray = [];
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

			$writeChildModulePayloadKeyArray = $writeChildPayloadKeyArray;
			array_push(
				$writeChildModulePayloadKeyArray,
				$writeModule
			);

			$writeChildModulePayloadKey = $this->getPayloadKey(
				payloadKeyArray: $writeChildModulePayloadKeyArray
			);

			$dataExist = $this->httpObject->httpRequestObject->dataDecodeObject->isset(
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
				$isObject = $this->httpObject->httpRequestObject->dataDecodeObject->dataType(
					keyString: $writeChildModulePayloadKey
				) === 'Object';
			}

			$indexCount = ($isObject || $isObject === Constant::$NULL)
				? 1 : $this->httpObject->httpRequestObject->dataDecodeObject->count(
					keyString: $writeChildModulePayloadKey
				);

			if (isset($writeChildRequiredFieldArray[$writeModule])) {
				$writeChildModuleRequiredFieldArray = &$writeChildRequiredFieldArray[$writeModule];
			} else {
				$writeChildModuleRequiredFieldArray = &$writeChildRequiredFieldArray;
			}

			$writeChildModuleUseHierarchy = $writeChildUseHierarchy ?? $this->getUseHierarchy(
				sqlConfig: $writeChildModuleSqlConfig,
				keyword: 'useHierarchy'
			);

			for ($index = 0; $index < $indexCount; $index++) {
				$writeChildModuleCurrentPayloadKeyArray = $writeChildModulePayloadKeyArray;
				array_push(
					$writeChildModuleCurrentPayloadKeyArray,
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

				$dataExist = $this->httpObject->httpRequestObject->dataDecodeObject->isset(
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
					writeParentPayloadKeyArray: $writeChildModuleCurrentPayloadKeyArray,
					writeParentRequiredFieldArray: $writeChildModuleRequiredFieldArray,
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
			[$isValidData, $errorArray] = $this->validate(
				validationConfig: $sqlConfig['__VALIDATE__']
			);
			if ($isValidData !== Constant::$TRUE) {
				$this->httpObject->httpResponseObject->httpStatus = HttpStatus::$BadRequest;
				$response = $errorArray;
				$return = false;
			}
		}
		return $return;
	}
}
