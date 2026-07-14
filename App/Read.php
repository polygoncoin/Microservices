<?php

/**
 * Read APIs
 * php version 8.3
 *
 * @category  ReadAPI
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
use Microservices\App\Export;
use Microservices\App\Hook;
use Microservices\App\Http;
use Microservices\App\HttpStatus;

/**
 * Read APIs
 * php version 8.3
 *
 * @category  ReadAPIs
 * @package   Microservices
 * @author    Ramesh N. Jangid (Sharma) <polygon.co.in@gmail.com>
 * @copyright © 2026 Ramesh N. Jangid (Sharma)
 * @license   MIT https://opensource.org/license/mit
 * @link      https://github.com/polygoncoin/Microservices
 * @since     Class available since Release 1.0.0
 */
class Read
{
	use AppTrait;

	/**
	 * Hook object
	 *
	 * @var null|Hook
	 */
	private $hookObj = null;

	/**
	 * Data Encode object
	 *
	 * @var null|DataEncode
	 */
	public $dataEncodeObj = null;

	/**
	 * Fetch mode placeholder Function
	 *
	 * @var null|string
	 */
	public $fetchModePlaceholderFunction = null;

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
		$return = $this->processReadBasics(
			$readSqlConfig,
			$readUseResultSet
		);

		if ($return !== false) {
			return $return;
		}

		if (isset($readSqlConfig['__DOWNLOAD__'])) {
			return $this->download(
				readSqlConfig: $readSqlConfig
			);
		}

		// Check for cache
		$toBeCached = $this->getToBeCached(
			sqlConfig: $readSqlConfig
		);

		if (
			CommonFunction::isEnabled(
				httpObj: $this->httpObj,
				feature: 'customer_enabled_response_caching'
			)
			&& $toBeCached
		) {
			$this->dataEncodeObj = new DataEncode(
				httpObj: $this->httpObj
			);
			$this->dataEncodeObj->init(
				header: false
			);
		} else {
			$this->dataEncodeObj = &$this->httpObj->responseObj->dataEncodeObj;
		}

		// Set Server mode to execute query on - Read / Write Server
		$fetchFrom = $readSqlConfig['fetchFrom'] ?? 'Slave';
		$this->fetchModePlaceholderFunction = 'customer_' . strtolower($fetchFrom) . '_db_server_query_placeholder';
		$this->httpObj->requestObj->customerDbObj = DbCommonFunction::connectCustomerDb(
			customerData: $this->httpObj->requestObj->session['customerData'],
			fetchFrom: $fetchFrom
		);

		$this->read(
			readSqlConfig: $readSqlConfig,
			readUseResultSet: $readUseResultSet
		);

		if (
			CommonFunction::isEnabled(
				httpObj: $this->httpObj,
				feature: 'customer_enabled_response_caching'
			)
			&& $toBeCached
		) {
			$json = $this->dataEncodeObj->getData();
			$this->httpObj->requestObj->customerQueryCacheObj->queryCacheSet(
				customerId: $this->httpObj->requestObj->customerId,
				queryCacheKey: $readSqlConfig['queryCacheKey'],
				queryCacheValue: $json
			);
			$this->httpObj->responseObj->dataEncodeObj->appendData(
				data: $json
			);
		}

		return true;
	}

	/**
	 * Perform read operation
	 *
	 * @param array $readSqlConfig    Sql config
	 * @param bool  $readUseResultSet If true - Uses parent payload/results in child
	 *
	 * @return void
	 */
	private function read(
		&$readSqlConfig,
		$readUseResultSet
	): void {
		$this->httpObj->requestObj->session['requiredFieldArrCollection'] = $this->getRequired(
			sqlConfig: $readSqlConfig,
			flag: $readUseResultSet,
			isFirstCall: true
		);

		if (isset($this->httpObj->requestObj->session['requiredFieldArrCollection'])) {
			$this->httpObj->requestObj->session['requiredFieldArr'] = $this->httpObj->requestObj->session['requiredFieldArrCollection'];
		} else {
			$this->httpObj->requestObj->session['requiredFieldArr'] = [];
		}

		// Start Read operation
		for ($index = 0; $index < $indexCount; $index++) {
			$readPayloadKeyArr = [];
			if ($index === 0) {
				if ($this->httpObj->requestObj->session['payloadType'] === 'Array') {
					$readPayloadKeyArr[] = "{$index}";
				} else {
					$readPayloadKeyArr[] = '';
				}
			} else {
				$readPayloadKeyArr[] = "{$index}";
			}

			$arr = [];
			$arr['Status'] = HttpStatus::$Ok;
			if (
				CommonFunction::isEnabled(
					httpObj: $this->httpObj,
					feature: 'customer_enabled_payload_in_response'
				)
			) {
				$arr[Env::$payloadKeyInResponse] = $this->httpObj->requestObj->dataDecodeObj->getCompleteArray(
					keyString: implode(
						separator: ':',
						array: $readPayloadKeyArr
					)
				);
			}

			$this->readParent(
				readParentSqlConfig: $readSqlConfig,
				readParentPayloadKeyArr: $readPayloadKeyArr,
				readParentRequiredFieldArr: $this->httpObj->requestObj->session['requiredFieldArrCollection'],
				readParentUseResultSet: $readUseResultSet,
				readParentIsFirstCall: true
			);

			if ($this->httpObj->responseObj->httpStatus === HttpStatus::$Ok) {
				$arr['PayloadResponse'] = $response;
			} else { // Failure
				$arr['Error'] = $response;
			}
		}
	}

	/**
	 * Process Read Parent Config Function
	 *
	 * @param array $readParentSqlConfig    Sql config
	 * @param bool  $readIsFirstCall  true to represent the first call in recursion
	 * @param bool  $readUseResultSet If true - Uses parent payload/results in child
	 *
	 * @return void
	 */
	private function readParent(
		&$readParentSqlConfig,
		&$readParentPayloadKeyArr,
		&$readParentRequiredFieldArr,
		$readParentUseResultSet,
		$readParentIsFirstCall
	): void {
		$readParentPayloadKey = is_array(
			value: $readParentPayloadKeyArr
		) ? trim(
			string: implode(
				separator: ':',
				array: $readParentPayloadKeyArr
			),
			characters: ':'
		) : null;

		$isObject = null;
		if ($readParentPayloadKey !== null) {
			$isObject = $this->httpObj->requestObj->dataDecodeObj->dataType(
				keyString: $readParentPayloadKey
			) === 'Object';
		}

		$indexCount = ($isObject || $isObject === null)
			? 1 : $this->httpObj->requestObj->dataDecodeObj->count(
				keyString: $readParentPayloadKey
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
				$readParentCurrentResponse = &$readParentResponse;
			} else {
				$readParentResponse[$index] = [];
				$readParentCurrentResponse = &$readParentResponse[$index];
			}
			$readParentCurrentPayloadKeyArr = $readParentPayloadKeyArr;

			if (
				!$isObject
				&& !$readParentUseHierarchy
			) {
				array_push(
					$readParentCurrentPayloadKeyArr,
					$index
				);
			}

			$readParentCurrentPayloadKey = is_array(
				value: $readParentCurrentPayloadKeyArr
			) ? implode(
				separator: ':',
				array: $readParentCurrentPayloadKeyArr
			) : '';

			if (
				!$this->httpObj->requestObj->dataDecodeObj->isset(
					keyString: $readParentCurrentPayloadKey
				)
			) {
				if ($readParentUseHierarchy) {
					throw new \Exception(
						message: "Payload key '{$readParentCurrentPayloadKey}' not set",
						code: HttpStatus::$NotFound
					);
				} else {
					continue;
				}
			}

			// Load Payload
			$this->httpObj->requestObj->session['payload'] = $this->httpObj->requestObj->dataDecodeObj->get(
				keyString: $readParentCurrentPayloadKey
			);

			if (count(value: $readParentRequiredFieldArr)) {
				$this->httpObj->requestObj->session['requiredFieldArr'] = $readParentRequiredFieldArr;
			} else {
				$this->httpObj->requestObj->session['requiredFieldArr'] = [];
			}

			if (
				Env::$enableGlobalCounter
				&& isset($readParentSqlConfig['__VARIABLES__']['__GLOBAL_COUNTER__'])
			) {
				$readParentSqlConfig['__VARIABLES__']['__GLOBAL_COUNTER__'] = Counter::getGlobalCounter();
			}

			// Validation
			if (
				isset($readParentSqlConfig['__VALIDATE__'])
				&& !$this->isValidPayload(
					sqlConfig: $readParentSqlConfig,
					response: $readParentCurrentResponse
				)
			) {
				continue;
			}

			// Execute - Pre Hook
			if (isset($readParentSqlConfig['__PRE-SQL-HOOKS__'])) {
				if ($this->hookObj === null) {
					$this->hookObj = new Hook(
						httpObj: $this->httpObj
					);
				}
				$this->hookObj->triggerHook(
					hookArr: $readParentSqlConfig['__PRE-SQL-HOOKS__']
				);
			}

			// Execute
			switch ($readParentSqlConfig['__MODE__']) {
				// Query will return single dbFetchedRecord
				case 'singleRecordFormat':
					if ($readParentIsFirstCall) {
						$this->dataEncodeObj->startObject(
							objectKey: 'Results'
						);
					} else {
						$this->dataEncodeObj->startObject();
					}
					$this->fetchSingleRecord(
						readSqlConfig: $readParentSqlConfig,
						readPayloadKeyArr: $readParentPayloadKeyArr,
						readUseResultSet: $readParentUseResultSet,
						readIsFirstCall: $readParentIsFirstCall
					);
					$this->dataEncodeObj->endObject();
					break;
				// Query will return multiple rows
				case 'multipleRecordFormat':
					if ($readParentIsFirstCall) {
						if (isset($readParentSqlConfig['countQuery'])) {
							$this->dataEncodeObj->startObject(
								objectKey: 'Results'
							);
							$this->fetchRecordCount(
								readSqlConfig: $readParentSqlConfig
							);
							$this->dataEncodeObj->startArray(
								objectKey: 'Data'
							);
						} else {
							$this->dataEncodeObj->startArray(
								objectKey: 'Results'
							);
						}
					} else {
						$this->dataEncodeObj->startArray(
							objectKey: $readParentPayloadKeyArr[
								count(
									value: $readParentPayloadKeyArr
								) - 1
							]
						);
					}
					$this->fetchMultipleRecords(
						readSqlConfig: $readParentSqlConfig,
						readPayloadKeyArr: $readParentPayloadKeyArr,
						readUseResultSet: $readParentUseResultSet,
						readIsFirstCall: $readParentIsFirstCall
					);
					$this->dataEncodeObj->endArray();
					if (
						$readParentIsFirstCall
						&& isset($readParentSqlConfig['countQuery'])
					) {
						$this->dataEncodeObj->endObject();
					}
					break;
			}

			// Triggers
			if (isset($readParentSqlConfig['__TRIGGERS__'])) {
				$this->dataEncodeObj->addKeyData(
					objectKey: '__TRIGGERS__',
					data: $this->getTriggerData(
						triggerConfig: $readParentSqlConfig['__TRIGGERS__']
					)
				);
			}

			// Execute - Post Hook
			if (isset($readParentSqlConfig['__POST-SQL-HOOKS__'])) {
				if ($this->hookObj === null) {
					$this->hookObj = new Hook(
						httpObj: $this->httpObj
					);
				}
				$this->hookObj->triggerHook(
					hookArr: $readParentSqlConfig['__POST-SQL-HOOKS__']
				);
			}
		}
	}

	/**
	 * Process Read Child Config Function
	 *
	 * @param array $readSqlConfig    Sql config
	 * @param array $readPayloadKeyArr
	 * @param array $dbFetchedRecord          Record data fetched from DB
	 * @param bool  $readUseResultSet If true - Uses parent payload/results in child
	 *
	 * @return void
	 */
	private function readChild(
		&$readChildSqlConfig,
		&$readChildPayloadKeyArr,
		$dbFetchedRecord,
		$readChildUseResultSet
	): void {
		if ($readChildUseHierarchy) {
			// $record = $this->httpObj->requestObj->session['payload'];
			$this->resetFetchData(
				fetchFrom: 'sqlPayload',
				moduleKeyArr: $readChildPayloadKeyArr,
				record: $dbFetchedRecord
			);
		}

		if (
			isset($readChildPayloadKeyArr[0])
			&& $readChildPayloadKeyArr[0] === ''
		) {
			$readChildPayloadKeyArr = array_shift(
				$readChildPayloadKeyArr
			);
		}
		if (!is_array(value: $readChildPayloadKeyArr)) {
			$readChildPayloadKeyArr = [];
		}

		if (
			!(
				isset($readChildSqlConfig['__SUB-QUERY__'])
				&& !$this->isObject(
					arr: $readChildSqlConfig['__SUB-QUERY__']
				)
			)
		) {
			return;
		}

		foreach ($readChildSqlConfig['__SUB-QUERY__'] as $readModule => &$readChildModuleSqlConfig) {
			$dataExist = false;

			$readChildResponse[$readModule] = [];
			$readChildModuleResponse = &$readChildResponse[$readModule];
			
			$readChildModulePayloadKeyArr = $readChildPayloadKeyArr;
			array_push(
				$readChildModulePayloadKeyArr,
				$readModule
			);

			$readChildModulePayloadKey = is_array(
				value: $readChildModulePayloadKeyArr
			) ? implode(
				separator: ':',
				array: $readChildModulePayloadKeyArr
			) : null;

			$dataExist = $this->httpObj->requestObj->dataDecodeObj->isset(
				keyString: $readChildModulePayloadKey
			);
			if (
				$readChildUseHierarchy
				&& !$dataExist
			) { // use parent data of a payload
				throw new \Exception(
					message: "Invalid payload: Module '{$readModule}' missing",
					code: HttpStatus::$NotFound
				);
			}
			if ($dataExist) {
				return;
			}

			$isObject = null;
			if ($readChildModulePayloadKey !== null) {
				$isObject = $this->httpObj->requestObj->dataDecodeObj->dataType(
					keyString: $readChildModulePayloadKey
				) === 'Object';
			}

			$indexCount = ($isObject || $isObject === null)
				? 1 : $this->httpObj->requestObj->dataDecodeObj->count(
					keyString: $readChildModulePayloadKey
				);

			if (isset($readChildRequiredFieldArr[$readModule])) {
				$readChildModuleRequiredFieldArr = &$readChildRequiredFieldArr[$readModule];
			} else {
				$readChildModuleRequiredFieldArr = &$readChildRequiredFieldArr;
			}

			$readChildModuleUseHierarchy = $readChildUseHierarchy ?? $this->getUseHierarchy(
				sqlConfig: $readChildModuleSqlConfig,
				keyword: 'useHierarchy'
			);

			for ($index = 0; $index < $indexCount; $index++) {
				$readChildModuleCurrentPayloadKeyArr = $readChildModulePayloadKeyArr;
				array_push(
					$readChildModuleCurrentPayloadKeyArr,
					$readModule
				);

				$readChildModuleCurrentResponse = &$readChildModuleResponse;
				$readChildModuleCurrentResponse[$index] = [];
				$readChildModuleCurrentResponse = &$readChildCurrentResponse[$index];

				if (
					$isObject
					|| $isObject === null
				) {
					$readChildModuleCurrentPayloadKey = $readChildModulePayloadKey;
				} else {
					$readChildModuleCurrentPayloadKey = "{$readChildModulePayloadKey}:{$index}";
				}

				$dataExist = $this->httpObj->requestObj->dataDecodeObj->isset(
					keyString: $readChildModuleCurrentPayloadKey
				);

				if (
					$readChildModuleUseHierarchy
					&& !$dataExist
				) { // use parent data of a payload
					throw new \Exception(
						message: "Invalid payload: Module '{$readModule}' missing",
						code: HttpStatus::$NotFound
					);
				}

				if (!$dataExist) {
					continue;
				}

				$this->readParent(
					readParentSqlConfig: $readChildModuleSqlConfig,
					readParentPayloadKeyArr: $readChildModulePayloadKeyArr,
					readParentRequiredFieldArr: $readChildModuleCurrentPayloadKeyArr,
					readParentUseResultSet: $readChildModuleUseResultSet,
					readParentIsFirstCall: false
				);
			}
		}
	}

	/**
	 * Fetch dbFetchedRecord count
	 *
	 * @param array $readSqlConfig Sql config
	 *
	 * @return void
	 * @throws \Exception
	 */
	private function fetchRecordCount(
		$readSqlConfig
	): void {
		if (!isset($readSqlConfig['countQuery'])) {
			return;
		}
		$readSqlConfig['__QUERY__'] = $readSqlConfig['countQuery'];
		if (isset($readSqlConfig['__COUNT-SQL-COMMENT__'])) {
			$readSqlConfig['__SQL-COMMENT__'] = $readSqlConfig['__COUNT-SQL-COMMENT__'];
		}
		unset($readSqlConfig['__COUNT-SQL-COMMENT__']);
		unset($readSqlConfig['countQuery']);

		$this->httpObj->requestObj->session['queryParamArr']['page']  = $this->httpObj->httpReqData['get']['page'] ?? 1;
		$this->httpObj->requestObj->session['queryParamArr']['perPage']  = $this->httpObj->httpReqData['get']['perPage'] ??
			Env::$defaultPerPage;

		if ($this->httpObj->requestObj->session['queryParamArr']['perPage'] > Env::$maxResultsPerPage) {
			throw new \Exception(
				message: 'perPage exceeds max perPage value of ' . Env::$maxResultsPerPage,
				code: HttpStatus::$Forbidden
			);
		}

		$this->httpObj->requestObj->session['queryParamArr']['start'] = (
			($this->httpObj->requestObj->session['queryParamArr']['page'] - 1) *
			$this->httpObj->requestObj->session['queryParamArr']['perPage']
		);

		$mode = getenv(name: $this->httpObj->requestObj->session['customerData'][$this->fetchModePlaceholderFunction]);
		$function = "getSqlAndParam{$mode}Mode";
		[$id, $sql, $paramArr, $errorArr, $missExecution] = $this->$function(
			sqlConfig: $readSqlConfig
		);

		if (!empty($errorArr)) {
			throw new \Exception(
				message: $errorArr,
				code: HttpStatus::$InternalServerError
			);
		}

		if ($missExecution) {
			return;
		}

		$this->httpObj->requestObj->customerDbObj->execQuery(
			sql: $sql,
			paramArr: $paramArr
		);
		$dbFetchedRecord = $this->httpObj->requestObj->customerDbObj->fetch();
		$this->httpObj->requestObj->customerDbObj->closeCursor();

		$totalRecordsCount = isset($dbFetchedRecord['count']) ? $dbFetchedRecord['count'] : 0;
		$totalPages = ceil(
			num: $totalRecordsCount / $this->httpObj->requestObj->session['queryParamArr']['perPage']
		);

		$this->dataEncodeObj->addKeyData(
			objectKey: 'page',
			data: $this->httpObj->requestObj->session['queryParamArr']['page']
		);
		$this->dataEncodeObj->addKeyData(
			objectKey: 'perPage',
			data: $this->httpObj->requestObj->session['queryParamArr']['perPage']
		);
		$this->dataEncodeObj->addKeyData(
			objectKey: 'totalPages',
			data: $totalPages
		);
		$this->dataEncodeObj->addKeyData(
			objectKey: 'totalRecords',
			data: $totalRecordsCount
		);
	}

	/**
	 * Fetch single record
	 *
	 * @param array $readSqlConfig     Sql config
	 * @param array $readPayloadKeyArr
	 * @param bool  $readUseResultSet  If true - Uses parent payload/results in child
	 * @param bool  $readIsFirstCall   true to represent the first call in recursion
	 *
	 * @return void
	 * @throws \Exception
	 */
	private function fetchSingleRecord(
		&$readSqlConfig,
		&$readPayloadKeyArr,
		$readUseResultSet,
		$readIsFirstCall
	): void {
		$mode = getenv(name: $this->httpObj->requestObj->session['customerData'][$this->fetchModePlaceholderFunction]);
		$function = "getSqlAndParam{$mode}Mode";
		[$id, $sql, $paramArr, $errorArr, $missExecution] = $this->$function(
			sqlConfig: $readSqlConfig,
			payloadKeyArr: $readPayloadKeyArr
		);

		if (!empty($errorArr)) {
			throw new \Exception(
				message: $errorArr,
				code: HttpStatus::$InternalServerError
			);
		}

		if ($missExecution) {
			return;
		}

		$this->httpObj->requestObj->customerDbObj->execQuery(
			sql: $sql,
			paramArr: $paramArr
		);
		if ($dbFetchedRecord = $this->httpObj->requestObj->customerDbObj->fetch()) {
			foreach ($dbFetchedRecord as $objectKey => &$objectKeyValue) {
				$this->dataEncodeObj->addKeyData(
					objectKey: $objectKey,
					data: $objectKeyValue
				);
			}
			// check if selected column-name mismatches or conflicts with
			// configured module/submodule names
			if (isset($readSqlConfig['__SUB-QUERY__'])) {
				$subQueryKeyArr = array_keys(
					array: $readSqlConfig['__SUB-QUERY__']
				);
				foreach ($dbFetchedRecord as $objectKey => &$objectKeyValue) {
					if (
						in_array(
							needle: $objectKey,
							haystack: $subQueryKeyArr,
							strict: true
						)
					) {
						throw new \Exception(
							message: 'Invalid config: Conflicting column names',
							code: HttpStatus::$InternalServerError
						);
					}
				}
			}
		} else {
			if ($readIsFirstCall) {
				$this->httpObj->responseObj->httpStatus = HttpStatus::$NotFound;
				return;
			}
		}
		$this->httpObj->requestObj->customerDbObj->closeCursor();

		if (isset($readSqlConfig['__SUB-QUERY__'])) {
			$this->readChild(
				readSqlConfig: $readSqlConfig,
				readPayloadKeyArr: $readPayloadKeyArr,
				dbFetchedRecord: $dbFetchedRecord,
				readUseResultSet: $readUseResultSet
			);
		}
	}

	/**
	 * Fetch multiple record
	 *
	 * @param array $readSqlConfig    Sql config
	 * @param array $readPayloadKeyArr
	 * @param bool  $readUseResultSet If true - Uses parent payload/results in child
	 * @param bool  $readIsFirstCall  true to represent first call in recursion
	 *
	 * @return void
	 * @throws \Exception
	 */
	private function fetchMultipleRecords(
		&$readSqlConfig,
		&$readPayloadKeyArr,
		$readUseResultSet,
		$readIsFirstCall
	): void {
		$mode = getenv(name: $this->httpObj->requestObj->session['customerData'][$this->fetchModePlaceholderFunction]);
		$function = "getSqlAndParam{$mode}Mode";
		[$id, $sql, $paramArr, $errorArr, $missExecution] = $this->$function(
			sqlConfig: $readSqlConfig,
			payloadKeyArr: $readPayloadKeyArr
		);

		if (!empty($errorArr)) {
			throw new \Exception(
				message: $errorArr,
				code: HttpStatus::$InternalServerError
			);
		}

		if ($missExecution) {
			return;
		}

		if ($readIsFirstCall) {
			if (isset($this->httpObj->requestObj->session['queryParamArr']['orderBy'])) {
				$orderByStrArr = [];
				$orderByArr = CommonFunction::jsonDecode(
					value: $this->httpObj->requestObj->session['queryParamArr']['orderBy']
				);
				foreach ($orderByArr as $orderByKey => &$orderByKeyValue) {
					$orderByKey = str_replace(
						search: ['`', ' '],
						replace: '',
						subject: $orderByKey
					);
					$orderByKeyValue = strtoupper(
						string: $orderByKeyValue
					);
					if (
						in_array(
							needle: $orderByKeyValue,
							haystack: ['ASC', 'DESC'],
							strict: true
						)
					) {
						$orderByStrArr[] = "`{$orderByKey}` {$orderByKeyValue}";
					}
				}
				if (
					count(
						value: $orderByStrArr
					) > 0
				) {
					$sql .= ' ORDER BY ' . implode(
						separator: ', ',
						array: $orderByStrArr
					);
				}
			}
		}

		if (isset($readSqlConfig['countQuery'])) {
			$start = $this->httpObj->requestObj->session['queryParamArr']['start'];
			$offset = $this->httpObj->requestObj->session['queryParamArr']['perPage'];
			$sql .= " LIMIT {$start}, {$offset}";
		}

		$this->httpObj->requestObj->customerDbObj->execQuery(
			sql: $sql,
			paramArr: $paramArr,
			pushPop: $pushPop
		);

		$pushPop = true;
		$singleColumn = false;
		for ($index = 0; $dbFetchedRecord = $this->httpObj->requestObj->customerDbObj->fetch(); $index++) {
			if ($index === 0) {
				if (
					count(
						value: $dbFetchedRecord
					) === 1
				) {
					$singleColumn = true;
				}
				$singleColumn = $singleColumn
					&& !isset($readSqlConfig['__SUB-QUERY__']);
			}
			if ($singleColumn) {
				$this->dataEncodeObj->encode(
					data: $dbFetchedRecord[
						key(
							array: $dbFetchedRecord
						)
					]
				);
			} elseif (isset($readSqlConfig['__SUB-QUERY__'])) {
				$this->dataEncodeObj->startObject();
				foreach ($dbFetchedRecord as $rowKey => &$rowKeyValue) {
					$this->dataEncodeObj->addKeyData(
						objectKey: $rowKey,
						data: $rowKeyValue
					);
				}
				$this->readChild(
					readSqlConfig: $readSqlConfig,
					readPayloadKeyArr: $readPayloadKeyArr,
					dbFetchedRecord: $dbFetchedRecord,
					readUseResultSet: $readUseResultSet
				);
				$this->dataEncodeObj->endObject();
			} else {
				$this->dataEncodeObj->encode(
					data: $dbFetchedRecord
				);
			}
		}
		$this->httpObj->requestObj->customerDbObj->closeCursor(
			pushPop: $pushPop
		);
	}

	/**
	 * Explain read configuration
	 *
	 * @param array $readSqlConfig    Sql config
	 * @param bool  $readUseResultSet If true - Uses parent payload/results in child
	 *
	 * @return bool
	 */
	private function explain(
		&$readSqlConfig,
		$readUseResultSet
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
				sqlConfig: $readSqlConfig,
				flag: $readUseResultSet,
				isFirstCall: true
			)
		);
		$this->dataEncodeObj->endObject();

		return true;
	}

	/**
	 * Download data
	 *
	 * @param array $readSqlConfig Sql config
	 *
	 * @return array
	 */
	private function download(
		$readSqlConfig
	): array {
		$return = [[], '', HttpStatus::$Ok];

		if (
			!CommonFunction::isEnabled(
				httpObj: $this->httpObj,
				feature: 'customer_enabled_download_request'
			)
		) {
			return [[], '', HttpStatus::$NotFound];
		}

		$mode = getenv(name: $this->httpObj->requestObj->session['customerData'][$this->fetchModePlaceholderFunction]);
		$function = "getSqlAndParam{$mode}Mode";
		[$id, $sql, $paramArr, $errorArr, $missExecution] = $this->$function(
			sqlConfig: $readSqlConfig
		);
		$serverMode = $readSqlConfig['fetchFrom'] ?? 'Slave';

		$exportDbData = [];
		switch ($serverMode) {
			case 'Master':
				$exportDbData = DbCommonFunction::customerMasterDatabaseServerCred(
					customerData: $this->httpObj->requestObj->session['customerData']
				);
				break;
			case 'Slave':
				$exportDbData = DbCommonFunction::customerSlaveDatabaseServerCred(
					customerData: $this->httpObj->requestObj->session['customerData']
				);
				break;
		}

		// Export
		$export = new Export(
			httpObj: $this->httpObj,
			dbServerType: $exportDbData['dbServerType']
		);
		$export->init(
			dbServerHostname: $exportDbData['dbServerHostname'],
			dbServerPort: $exportDbData['dbServerPort'],
			dbServerUsername: $exportDbData['dbServerUsername'],
			dbServerPassword: $exportDbData['dbServerPassword'],
			dbServerDatabase: $exportDbData['dbServerDatabase']
		);

		if (isset($readSqlConfig['downloadFile'])) {
			$downloadFile = date('Ymd-His') . '-' . $readSqlConfig['downloadFile'];
			if (
				isset($readSqlConfig['exportFile'])
				&& !empty($readSqlConfig['exportFile'])
			) {
				$return = $export->initDownload(
					downloadFile: $downloadFile,
					sql: $sql,
					paramArr: $paramArr,
					exportFile: $readSqlConfig['exportFile']
				);
			} else {
				$return = $export->initDownload(
					downloadFile: $downloadFile,
					sql: $sql,
					paramArr: $paramArr
				);
			}
		} else {
			if (isset($readSqlConfig['exportFile'])) {
				$return = $export->saveExport(
					sql: $sql,
					paramArr: $paramArr,
					exportFile: $readSqlConfig['exportFile']
				);
			}
		}

		return $return;
	}
}
