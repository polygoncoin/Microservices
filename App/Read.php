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
use Microservices\App\Constant;
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
	 * Placeholder Mode
	 * 
	 * @var null|string
	 */
	public $placeholderMode = null;

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
		$return = $this->readBasics(
			$sqlConfig,
			$useResultSet
		);

		if ($return !== Constant::$FALSE) {
			return $return;
		}

		if (isset($sqlConfig['__DOWNLOAD__'])) {
			return $this->download(
				readSqlConfig: $sqlConfig
			);
		}

		// Check for cache
		$toBeCached = $this->getToBeCached(
			sqlConfig: $sqlConfig
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
				header: Constant::$FALSE
			);
		} else {
			$this->dataEncodeObj = &$this->httpObj->httpResponseObj->dataEncodeObj;
		}

		// Set Server mode to execute query on - Read / Write Server
		$fetchDbMode = $sqlConfig['fetchDbMode'] ?? 'Slave';
		$placeholderModeKey = 'customer_' . strtolower($fetchDbMode) . '_db_server_query_placeholder';
		$this->placeholderMode = getenv(name: $this->httpObj->httpRequestObj->activeRequestCollection['customerData'][$placeholderModeKey]);
		$this->httpObj->httpRequestObj->customerDbObj = DbCommonFunction::connectCustomerDb(
			customerData: $this->httpObj->httpRequestObj->activeRequestCollection['customerData'],
			fetchDbMode: $fetchDbMode
		);

		$this->read(
			readSqlConfig: $sqlConfig,
			readUseResultSet: $useResultSet
		);

		if (
			CommonFunction::isEnabled(
				httpObj: $this->httpObj,
				feature: 'customer_enabled_response_caching'
			)
			&& $toBeCached
		) {
			$json = $this->dataEncodeObj->getData();
			$this->httpObj->httpRequestObj->customerQueryCacheObj->queryCacheSet(
				customerId: $this->httpObj->httpRequestObj->customerId,
				queryCacheKey: $sqlConfig['queryCacheKey'],
				queryCacheValue: $json
			);
			$this->httpObj->httpResponseObj->dataEncodeObj->appendData(
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
		$this->httpObj->httpRequestObj->activeRequestCollection['requiredFieldArrCollection'] = $this->getRequired(
			sqlConfig: $readSqlConfig,
			flag: $readUseResultSet,
			isFirstCall: Constant::$TRUE
		);

		if (isset($this->httpObj->httpRequestObj->activeRequestCollection['requiredFieldArrCollection'])) {
			$this->httpObj->httpRequestObj->activeRequestCollection['requiredFieldArr'] = $this->httpObj->httpRequestObj->activeRequestCollection['requiredFieldArrCollection'];
		} else {
			$this->httpObj->httpRequestObj->activeRequestCollection['requiredFieldArr'] = [];
		}

		$indexCount = $this->httpObj->httpRequestObj->activeRequestCollection['payloadType'] === 'Array'
			? $this->httpObj->httpRequestObj->dataDecodeObj->count() : 1;

		// Start Read operation
		$readPayloadKeyArr = [];
		for ($index = 0; $index < $indexCount; $index++) {
			$readCurrentPayloadKeyArr = $readPayloadKeyArr;

			if ($index === 0) {
				if ($this->httpObj->httpRequestObj->activeRequestCollection['payloadType'] === 'Array') {
					$readCurrentPayloadKeyArr[] = "{$index}";
				} else {
					$readCurrentPayloadKeyArr[] = '';
				}
			} else {
				$readCurrentPayloadKeyArr[] = "{$index}";
			}

			$this->readParent(
				readParentSqlConfig: $readSqlConfig,
				readParentPayloadKeyArr: $readCurrentPayloadKeyArr,
				readParentRequiredFieldArr: $this->httpObj->httpRequestObj->activeRequestCollection['requiredFieldArrCollection'],
				readParentUseResultSet: $readUseResultSet,
				readParentIsFirstCall: Constant::$TRUE
			);
		}
	}

	/**
	 * Process Read Parent Config Function
	 * 
	 * @param array $readParentSqlConfig    Sql config
	 * @param array $readParentPayloadKeyArr
	 * @param array $readParentRequiredFieldArr
	 * @param bool  $readUseResultSet If true - Uses parent payload/results in child
	 * @param bool  $readIsFirstCall  true to represent the first call in recursion
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
		if ($readParentPayloadKey !== Constant::$NULL) {
			$isObject = $this->httpObj->httpRequestObj->dataDecodeObj->dataType(
				keyString: $readParentPayloadKey
			) === 'Object';
		}

		$indexCount = ($isObject || $isObject === Constant::$NULL)
			? 1 : $this->httpObj->httpRequestObj->dataDecodeObj->count(
				keyString: $readParentPayloadKey
			);

		$mode = getenv(name: $this->httpObj->httpRequestObj->activeRequestCollection['customerData']['customer_master_db_server_query_placeholder']);
		$function = "getSqlAndParam{$mode}Mode";

		for ($index = 0; $index < $indexCount; $index++) {
			if (
				$isObject
				&& $index > 0
			) {
				return;
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
				!$this->httpObj->httpRequestObj->dataDecodeObj->isset(
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
			$this->httpObj->httpRequestObj->activeRequestCollection['payload'] = $this->httpObj->httpRequestObj->dataDecodeObj->get(
				keyString: $readParentCurrentPayloadKey
			);

			if (count(value: $readParentRequiredFieldArr)) {
				$this->httpObj->httpRequestObj->activeRequestCollection['requiredFieldArr'] = $readParentRequiredFieldArr;
			} else {
				$this->httpObj->httpRequestObj->activeRequestCollection['requiredFieldArr'] = [];
			}

			if (
				Env::$enableGlobalCounter
				&& isset($readParentSqlConfig['__VARIABLES__']['__GLOBAL_COUNTER__'])
			) {
				$readParentSqlConfig['__VARIABLES__']['__GLOBAL_COUNTER__'] = Counter::getGlobalCounter();
			}

			// Execute - Pre Hook
			if (isset($readParentSqlConfig['__PRE-SQL-HOOKS__'])) {
				if ($this->hookObj === Constant::$NULL) {
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
			// $arr = [];
			// $arr['Status'] = HttpStatus::$Ok;
			// if (
			// 	CommonFunction::isEnabled(
			// 		httpObj: $this->httpObj,
			// 		feature: 'customer_enabled_payload_in_response'
			// 	)
			// ) {
			// 	$arr[Env::$payloadKeyInResponse] = $this->httpObj->httpRequestObj->dataDecodeObj->getCompleteArray(
			// 		keyString: implode(
			// 			separator: ':',
			// 			array: $readCurrentPayloadKeyArr
			// 		)
			// 	);
			// }
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
						} else {
							$this->dataEncodeObj->startArray(
								objectKey: 'Results'
							);
						}
			// $arr = [];
			// $arr['Status'] = HttpStatus::$Ok;
			// if (
			// 	CommonFunction::isEnabled(
			// 		httpObj: $this->httpObj,
			// 		feature: 'customer_enabled_payload_in_response'
			// 	)
			// ) {
			// 	$arr[Env::$payloadKeyInResponse] = $this->httpObj->httpRequestObj->dataDecodeObj->getCompleteArray(
			// 		keyString: implode(
			// 			separator: ':',
			// 			array: $readCurrentPayloadKeyArr
			// 		)
			// 	);
			// }
						if (isset($readParentSqlConfig['countQuery'])) {
							$this->fetchRecordCount(
								readSqlConfig: $readParentSqlConfig
							);
							$this->dataEncodeObj->startArray(
								objectKey: 'Data'
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
				if ($this->hookObj === Constant::$NULL) {
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
		&$dbFetchedRecord,
		$readChildUseResultSet
	): void {
		if ($readChildUseResultSet) {
			$this->resetFetchData(
				activeRequestCollectionKey: 'sqlPayload',
				payloadKeyArr: $readChildPayloadKeyArr,
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

			$dataExist = $this->httpObj->httpRequestObj->dataDecodeObj->isset(
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
			if ($readChildModulePayloadKey !== Constant::$NULL) {
				$isObject = $this->httpObj->httpRequestObj->dataDecodeObj->dataType(
					keyString: $readChildModulePayloadKey
				) === 'Object';
			}

			$indexCount = ($isObject || $isObject === Constant::$NULL)
				? 1 : $this->httpObj->httpRequestObj->dataDecodeObj->count(
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

				if (
					$isObject
					|| $isObject === Constant::$NULL
				) {
					$readChildModuleCurrentPayloadKey = $readChildModulePayloadKey;
				} else {
					$readChildModuleCurrentPayloadKey = "{$readChildModulePayloadKey}:{$index}";
				}

				$dataExist = $this->httpObj->httpRequestObj->dataDecodeObj->isset(
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
					readParentIsFirstCall: Constant::$FALSE
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

		$this->httpObj->httpRequestObj->activeRequestCollection['queryParamArr']['page']  = $this->httpObj->httpRequestObj->activeRequestCollection['payload']['page'] ?? 1;
		$this->httpObj->httpRequestObj->activeRequestCollection['queryParamArr']['perPage']  = $this->httpObj->httpRequestObj->activeRequestCollection['payload']['perPage'] ??
			Env::$defaultPerPage;

		if ($this->httpObj->httpRequestObj->activeRequestCollection['queryParamArr']['perPage'] > Env::$maxResultsPerPage) {
			throw new \Exception(
				message: 'perPage exceeds max perPage value of ' . Env::$maxResultsPerPage,
				code: HttpStatus::$Forbidden
			);
		}

		$this->httpObj->httpRequestObj->activeRequestCollection['queryParamArr']['start'] = (
			($this->httpObj->httpRequestObj->activeRequestCollection['queryParamArr']['page'] - 1) * 
			$this->httpObj->httpRequestObj->activeRequestCollection['queryParamArr']['perPage']
		);

		$function = "getSqlAndParam{$this->placeholderMode}Mode";
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

		$this->httpObj->httpRequestObj->customerDbObj->execQuery(
			sql: $sql,
			paramArr: $paramArr
		);
		$dbFetchedRecord = $this->httpObj->httpRequestObj->customerDbObj->fetch();
		$this->httpObj->httpRequestObj->customerDbObj->closeCursor();

		$totalRecordsCount = isset($dbFetchedRecord['count']) ? $dbFetchedRecord['count'] : 0;
		$totalPages = ceil(
			num: $totalRecordsCount / $this->httpObj->httpRequestObj->activeRequestCollection['queryParamArr']['perPage']
		);

		$this->dataEncodeObj->addKeyData(
			objectKey: 'page',
			data: $this->httpObj->httpRequestObj->activeRequestCollection['queryParamArr']['page']
		);
		$this->dataEncodeObj->addKeyData(
			objectKey: 'perPage',
			data: $this->httpObj->httpRequestObj->activeRequestCollection['queryParamArr']['perPage']
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
		$function = "getSqlAndParam{$this->placeholderMode}Mode";
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

		$this->httpObj->httpRequestObj->customerDbObj->execQuery(
			sql: $sql,
			paramArr: $paramArr
		);
		if ($dbFetchedRecord = $this->httpObj->httpRequestObj->customerDbObj->fetch()) {
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
							strict: Constant::$TRUE
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
				$this->httpObj->httpResponseObj->httpStatus = HttpStatus::$NotFound;
				return;
			}
		}
		$this->httpObj->httpRequestObj->customerDbObj->closeCursor();

		if (isset($readSqlConfig['__SUB-QUERY__'])) {
			$this->readChild(
				readChildSqlConfig: $readSqlConfig,
				readChildPayloadKeyArr: $readPayloadKeyArr,
				dbFetchedRecord: $dbFetchedRecord,
				readChildUseResultSet: $readUseResultSet
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
		$function = "getSqlAndParam{$this->placeholderMode}Mode";

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
			if (isset($this->httpObj->httpRequestObj->activeRequestCollection['queryParamArr']['orderBy'])) {
				$orderByStrArr = [];
				$orderByArr = CommonFunction::jsonDecode(
					value: $this->httpObj->httpRequestObj->activeRequestCollection['queryParamArr']['orderBy']
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
							strict: Constant::$TRUE
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
			$start = $this->httpObj->httpRequestObj->activeRequestCollection['queryParamArr']['start'];
			$offset = $this->httpObj->httpRequestObj->activeRequestCollection['queryParamArr']['perPage'];
			$sql .= " LIMIT {$start}, {$offset}";
		}

		$pushPop = true;
		$this->httpObj->httpRequestObj->customerDbObj->execQuery(
			sql: $sql,
			paramArr: $paramArr,
			pushPop: $pushPop
		);

		$singleColumn = false;
		for ($index = 0; $dbFetchedRecord = $this->httpObj->httpRequestObj->customerDbObj->fetch(); $index++) {
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
					readChildSqlConfig: $readSqlConfig,
					readChildPayloadKeyArr: $readPayloadKeyArr,
					dbFetchedRecord: $dbFetchedRecord,
					readChildUseResultSet: $readUseResultSet
				);
				$this->dataEncodeObj->endObject();
			} else {
				$this->dataEncodeObj->encode(
					data: $dbFetchedRecord
				);
			}
		}
		$this->httpObj->httpRequestObj->customerDbObj->closeCursor(
			pushPop: $pushPop
		);
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

		$function = "getSqlAndParam{$this->placeholderMode}Mode";
		[$id, $sql, $paramArr, $errorArr, $missExecution] = $this->$function(
			sqlConfig: $readSqlConfig
		);
		$fetchDbMode = $readSqlConfig['fetchDbMode'] ?? 'Slave';

		$exportDbData = [];
		switch ($fetchDbMode) {
			case 'Master':
				$exportDbData = DbCommonFunction::customerMasterDatabaseServerCred(
					customerData: $this->httpObj->httpRequestObj->activeRequestCollection['customerData']
				);
				break;
			case 'Slave':
				$exportDbData = DbCommonFunction::customerSlaveDatabaseServerCred(
					customerData: $this->httpObj->httpRequestObj->activeRequestCollection['customerData']
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
