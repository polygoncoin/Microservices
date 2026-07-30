<?php

/**
 * Read / Write Trait
 * php version 8.3
 * 
 * @category  API
 * @package   Microservices
 * @author    Ramesh N. Jangid (Sharma) <polygon.co.in@gmail.com>
 * @copyright © 2026 Ramesh N. Jangid (Sharma)
 * @license   MIT https://opensource.org/license/mit
 * @link      https://github.com/polygoncoin/Microservices
 * @since     Class available since Release 1.0.0
 */

namespace Microservices\App;

use Microservices\App\CacheServerKey;
use Microservices\App\CommonFunction;
use Microservices\App\Counter;
use Microservices\App\Constant;
use Microservices\App\DatabaseServerDataType;
use Microservices\App\Env;
use Microservices\App\HttpStatus;
use Microservices\App\Start;
use Microservices\App\Validator;

/**
 * Trait for API
 * php version 8.3
 * 
 * @category  API_Trait
 * @package   Microservices
 * @author    Ramesh N. Jangid (Sharma) <polygon.co.in@gmail.com>
 * @copyright © 2026 Ramesh N. Jangid (Sharma)
 * @license   MIT https://opensource.org/license/mit
 * @link      https://github.com/polygoncoin/Microservices
 * @since     Class available since Release 1.0.0
 */
trait AppTrait
{
	/**
	 * Validator class object
	 * 
	 * @var null|Validator
	 */
	public $validatorObj = null;

	/**
	 * Function to help execute PHP functions enclosed with double quotes
	 * 
	 * @param mixed $param Returned values by PHP inbuilt functions
	 * 
	 * @return mixed
	 */
	public function execPhpFunc(
		$param
	): mixed {
		return $param;
	}

	/**
	 * Get required payload
	 * 
	 * @param array $sqlConfig   Sql config
	 * @param bool  $flag        useHierarchy / useResultSet flag
	 * @param bool  $isFirstCall true to represent the first call in recursion
	 * 
	 * @return array
	 * @throws \Exception
	 */
	private function getRequired(
		&$sqlConfig,
		$flag,
		$isFirstCall
	): array {
		$requiredFieldArr = [];

		foreach (['__PAYLOAD__', '__SET__', '__WHERE__'] as $option) {
			if (isset($sqlConfig[$option])) {
				foreach ($sqlConfig[$option] as $sqlParamConfig) {
					$activeRequestDataKey = $sqlParamConfig['activeRequestDataKey'];
					if ($activeRequestDataKey === 'function') {
						continue;
					}
					$isRequired = isset($sqlParamConfig['isRequired'])
						? $sqlParamConfig['isRequired'] : Constant::$FALSE;

					if ($isRequired) {
						$activeRequestDataKeySubKey = $sqlParamConfig['activeRequestDataKeySubKey'];

						if (!isset($requiredFieldArr[$activeRequestDataKey])) {
							$requiredFieldArr[$activeRequestDataKey] = [];
						}
						if (
							!in_array(
								needle: $activeRequestDataKeySubKey,
								haystack: $requiredFieldArr[$activeRequestDataKey],
								strict: Constant::$TRUE
							)
						) {
							$requiredFieldArr[$activeRequestDataKey][] = $activeRequestDataKeySubKey;
						}
					}
				}
			}
		}

		// Check for hierarchy setting
		$foundHierarchy = false;
		if (isset($sqlConfig['__WHERE__'])) {
			foreach ($sqlConfig['__WHERE__'] as $sqlParamConfig) {
				$activeRequestDataKey = $sqlParamConfig['activeRequestDataKey'];
				$activeRequestDataKeySubKey = $sqlParamConfig['activeRequestDataKeySubKey'];

				if (
					$isFirstCall
					&& in_array(
						needle: $activeRequestDataKey,
						haystack: ['sqlResults', 'sqlParamArr', 'sqlPayload'],
						strict: Constant::$TRUE
					)
				) {
					throw new \Exception(
						message: "First query can not have {$activeRequestDataKey} config",
						code: HttpStatus::$InternalServerError
					);
				}
				if (
					in_array(
						needle: $activeRequestDataKey,
						haystack: ['sqlResults', 'sqlParamArr', 'sqlPayload'],
						strict: Constant::$TRUE
					)
				) {
					$foundHierarchy = true;
					break;
				}
			}
			// if (
			// 	!$isFirstCall
			// 	&& $flag
			// 	&& !$foundHierarchy
			// ) {
			//     throw new \Exception(
			//          message: 'Invalid config: missing ' . $activeRequestDataKey,
			//          code: HttpStatus::$InternalServerError
			//      );
			// }
		}

		// Check in subQuery
		if (
			isset($sqlConfig['__SUB-QUERY__'])
			|| isset($sqlConfig['__SUB-PAYLOAD__'])
		) {
			if (
				isset($sqlConfig['__SUB-QUERY__'])
				&& !$this->isObject(
					arr: $sqlConfig['__SUB-QUERY__']
				)
			) {
				throw new \Exception(
					message: 'Sub-Query should be an associative array',
					code: HttpStatus::$InternalServerError
				);
			}
			if (
				isset($sqlConfig['__SUB-PAYLOAD__'])
				&& !$this->isObject(
					arr: $sqlConfig['__SUB-PAYLOAD__']
				)
			) {
				throw new \Exception(
					message: 'Sub-Payload should be an associative array',
					code: HttpStatus::$InternalServerError
				);
			}
			foreach (['__SUB-QUERY__', '__SUB-PAYLOAD__'] as $option) {
				if (isset($sqlConfig[$option])) {
					foreach ($sqlConfig[$option] as $module => &$moduleSqlConfig) {
						$flag = ($flag) ?? $this->getUseHierarchy(
							sqlConfig: $moduleSqlConfig
						);
						$moduleRequiredFieldArr = $this->getRequired(
							sqlConfig: $moduleSqlConfig,
							flag: $flag,
							isFirstCall: Constant::$FALSE
						);
						if ($flag) {
							$requiredFieldArr[$module] = $moduleRequiredFieldArr;
						} else {
							foreach ($moduleRequiredFieldArr as $activeRequestDataKey => &$activeRequestDataKeySubKeyArr) {
								if (!isset($requiredFieldArr[$activeRequestDataKey])) {
									$requiredFieldArr[$activeRequestDataKey] = [];
								}
								foreach ($activeRequestDataKeySubKeyArr as $activeRequestDataKeySubKey) {
									if (
										!in_array(
											needle: $activeRequestDataKeySubKey,
											haystack: $requiredFieldArr[$activeRequestDataKey],
											strict: Constant::$TRUE
										)
									) {
										$requiredFieldArr[$activeRequestDataKey][] = $activeRequestDataKeySubKey;
									}
								}
							}
						}
					}
				}
			}
		}

		return $requiredFieldArr;
	}

	/**
	 * Validate payload
	 * 
	 * @param array $validationConfig Validation config from Config file
	 * 
	 * @return array
	 */
	public function validate(&$validationConfig): array
	{
		if ($this->validatorObj === Constant::$NULL) {
			$this->validatorObj = new Validator(
				httpObj: $this->httpObj
			);
		}

		return $this->validatorObj->validate(
			validationConfig: $validationConfig
		);
	}

	/**
	 * Generate SQL query and its param's in Named format
	 * 
	 * @param array      $sqlConfig     Sql config
	 * @param array|null $payloadKeyArr Payload key's
	 * 
	 * @return array
	 */
	private function getSqlAndParamNamedMode(
		&$sqlConfig,
		$payloadKeyArr = null
	): array {
		$id = null;
		$sql = '';
		/*!999999 comment goes here */
		if (isset($sqlConfig['__SQL-COMMENT__'])) {
			$sql .= '/' . '*!999999 ';
			$sql .= $sqlConfig['__SQL-COMMENT__'];
			$sql .= ' */';
		}
		switch (true) {
			case isset($sqlConfig['__QUERY__']):
				$sql .= $sqlConfig['__QUERY__'];
				break;
			case isset($sqlConfig['__DOWNLOAD__']):
				$sql .= $sqlConfig['__DOWNLOAD__'];
				break;
		}
		$paramArr = [];
		$paramKeyArr = [];
		$errorArr = [];
		$record = [];
		$__SET__ = [];

		$missExecution = $wMissExecution = false;
		// Check __SET__
		if (
			isset($sqlConfig['__SET__'])
			&& count(
				value: $sqlConfig['__SET__']
			) !== 0
		) {
			$payloadVariableArr = $sqlConfig['__VARIABLES__'] ?? [];
			[$setParamArr, $errorArr, $missExecution] = $this->getSqlParam(
				sqlConfig: $sqlConfig['__SET__'],
				payloadVariableArr: $payloadVariableArr
			);
			if (
				empty($errorArr)
				&& !$missExecution
			) {
				if (!empty($setParamArr)) {
					// __SET__ not compulsory in query
					$found = strpos(
						haystack: $sql,
						needle: '__SET__'
					) !== Constant::$FALSE;
					foreach ($setParamArr as $paramKey => &$paramKeyValue) {
						$paramKey = str_replace(
							search: ['`', ' '],
							replace: '',
							subject: $paramKey
						);
						$paramKeyArr[] = $paramKey;
						if ($found) {
							$__SET__[] = "`{$paramKey}` = :{$paramKey}";
						}
						$paramArr[":{$paramKey}"] = $paramKeyValue;
						$record[$paramKey] = $paramKeyValue;
					}
				}
			}
		}

		// Check __WHERE__
		if (
			empty($errorArr)
			&& !$missExecution
			&& isset($sqlConfig['__WHERE__'])
			&& count(
				value: $sqlConfig['__WHERE__']
			) !== 0
		) {
			$wErrorArr = [];
			$payloadVariableArr = $sqlConfig['__VARIABLES__'] ?? [];
			[$whereParamArr, $wErrorArr, $wMissExecution] = $this->getSqlParam(
				sqlConfig: $sqlConfig['__WHERE__'],
				payloadVariableArr: $payloadVariableArr
			);
			if (
				empty($wErrorArr)
				&& !$wMissExecution
			) {
				if (!empty($whereParamArr)) {
					// __WHERE__ not compulsory in query
					$whereFound = strpos(
						haystack: $sql,
						needle: '__WHERE__'
					) !== Constant::$FALSE;
					if ($whereFound) {
						$__WHERE__ = [];
						foreach ($whereParamArr as $whereParamKey => &$whereParamKeyValue) {
							$whereParam = $whereParamKey = str_replace(
								search: ['`', ' '],
								replace: '',
								subject: $whereParamKey
							);
							$index = 0;
							while (
								in_array(
									needle: $whereParam,
									haystack: $paramKeyArr,
									strict: Constant::$TRUE
								)
							) {
								$index++;
								$whereParam = "{$whereParamKey}{$index}";
							}
							$paramKeyArr[] = $whereParam;
							$__WHERE__[] = "`{$whereParamKey}` = :{$whereParam}";
							$paramArr[":{$whereParam}"] = $whereParamKeyValue;
							$record[$whereParam] = $whereParamKeyValue;
						}
						$sql = str_replace(
							search: '__WHERE__',
							replace: implode(
								separator: ' AND ', array: $__WHERE__
							),
							subject: $sql
						);
					}
				}
			} else {
				$errorArr = array_merge($errorArr, $wErrorArr);
			}
		}
		if (!empty($__SET__)) {
			$sql = str_replace(
				search: '__SET__',
				replace: implode(
					separator: ', ', array: $__SET__
				),
				subject: $sql
			);
		}

		if (!empty($record)) {
			$this->resetFetchData(
				activeRequestDataKey: 'sqlParamArr',
				payloadKeyArr: $payloadKeyArr,
				record: $record
			);
		}

		return [$id, $sql, $paramArr, $errorArr, ($missExecution || $wMissExecution)];
	}

	/**
	 * Generate SQL query and its param's in Unnamed format
	 * 
	 * @param array      $sqlConfig     Sql config
	 * @param array|null $payloadKeyArr Payload key's
	 * 
	 * @return array
	 */
	private function getSqlAndParamUnnamedMode(
		&$sqlConfig,
		$payloadKeyArr = null
	): array {
		$id = null;
		$sql = '';
		/*!999999 comment goes here */
		if (isset($sqlConfig['__SQL-COMMENT__'])) {
			$sql .= '/' . '*!999999 ';
			$sql .= $sqlConfig['__SQL-COMMENT__'];
			$sql .= ' */';
		}
		switch (true) {
			case isset($sqlConfig['__QUERY__']):
				$sql .= $sqlConfig['__QUERY__'];
				break;
			case isset($sqlConfig['__DOWNLOAD__']):
				$sql .= $sqlConfig['__DOWNLOAD__'];
				break;
		}
		$paramArr = [];
		$paramKeyArr = [];
		$errorArr = [];
		$record = [];
		$__SET__ = [];

		$missExecution = $wMissExecution = false;
		// Check __SET__
		if (
			isset($sqlConfig['__SET__'])
			&& count(
				value: $sqlConfig['__SET__']
			) !== 0
		) {
			$payloadVariableArr = $sqlConfig['__VARIABLES__'] ?? [];
			[$setParamArr, $errorArr, $missExecution] = $this->getSqlParam(
				sqlConfig: $sqlConfig['__SET__'],
				payloadVariableArr: $payloadVariableArr
			);
			if (
				empty($errorArr)
				&& !$missExecution
			) {
				if (!empty($setParamArr)) {
					// __SET__ not compulsory in query
					$found = strpos(
						haystack: $sql,
						needle: '__SET__'
					) !== Constant::$FALSE;
					foreach ($setParamArr as $paramKey => &$paramKeyValue) {
						$paramKeyArr[] = $paramKey;
						if ($found) {
							$__SET__[] = "{$paramKey} = ?";
						}
						$paramArr[] = $paramKeyValue;
						$record[$paramKey] = $paramKeyValue;
					}
				}
			}
		}

		// Check __WHERE__
		if (
			empty($errorArr)
			&& !$missExecution
			&& isset($sqlConfig['__WHERE__'])
			&& count(
				value: $sqlConfig['__WHERE__']
			) !== 0
		) {
			$wErrorArr = [];
			$payloadVariableArr = $sqlConfig['__VARIABLES__'] ?? [];
			[$whereParamArr, $wErrorArr, $wMissExecution] = $this->getSqlParam(
				sqlConfig: $sqlConfig['__WHERE__'],
				payloadVariableArr: $payloadVariableArr
			);
			if (
				empty($wErrorArr)
				&& !$wMissExecution
			) {
				if (!empty($whereParamArr)) {
					// __WHERE__ not compulsory in query
					$whereFound = strpos(
						haystack: $sql,
						needle: '__WHERE__'
					) !== Constant::$FALSE;
					if ($whereFound) {
						$__WHERE__ = [];
						foreach ($whereParamArr as $whereParamKey => &$whereParamKeyValue) {
							$whereParam = $whereParamKey;
							$index = 0;
							while (
								in_array(
									needle: $whereParam,
									haystack: $paramKeyArr,
									strict: Constant::$TRUE
								)
							) {
								$index++;
								$whereParam = "{$whereParamKey}{$index}";
							}
							$paramKeyArr[] = $whereParam;
							$__WHERE__[] = "{$whereParamKey} = ?";
							$paramArr[] = $whereParamKeyValue;
							$record[$whereParam] = $whereParamKeyValue;
						}
						$sql = str_replace(
							search: '__WHERE__',
							replace: implode(
								separator: ' AND ', array: $__WHERE__
							),
							subject: $sql
						);
					}
				}
			} else {
				$errorArr = array_merge($errorArr, $wErrorArr);
			}
		}
		if (!empty($__SET__)) {
			$sql = str_replace(
				search: '__SET__',
				replace: implode(
					separator: ', ', array: $__SET__
				),
				subject: $sql
			);
		}

		if (!empty($record)) {
			$this->resetFetchData(
				activeRequestDataKey: 'sqlParamArr',
				payloadKeyArr: $payloadKeyArr,
				record: $record
			);
		}

		return [$id, $sql, $paramArr, $errorArr, ($missExecution || $wMissExecution)];
	}

	/**
	 * Generates ParamArr for statement to execute
	 * 
	 * @param array $sqlConfig          Sql config
	 * @param array $payloadVariableArr Payload Variables
	 * 
	 * @return array
	 * @throws \Exception
	 */
	private function getSqlParam(
		&$sqlConfig,
		&$payloadVariableArr
	): array {
		$missExecution = false;
		$paramArr = [];
		$errorArr = [];

		// Collect param values as per config respectively
		foreach ($sqlConfig as $sqlParamConfig) {
			$column = $sqlParamConfig['column'];
			$activeRequestDataKey = $sqlParamConfig['activeRequestDataKey'];
			$activeRequestDataKeySubKey = $sqlParamConfig['activeRequestDataKeySubKey'];
			if ($activeRequestDataKey === 'function') {
				$function = $activeRequestDataKeySubKey;
				$value = $function($this->httpObj->httpRequestObj->activeRequestData);
				$paramArr[$column] = $value;
				continue;
			} elseif (
				in_array(
					needle: $activeRequestDataKey,
					haystack: ['sqlParamArr', 'sqlPayload'],
					strict: Constant::$TRUE
				)
			) {
				if (!isset($this->httpObj->httpRequestObj->activeRequestData[$activeRequestDataKey])) {
					$errorArr[] = "Missing key '{$activeRequestDataKeySubKey}' in '{$activeRequestDataKey}'";
					continue;
				}
				$activeRequestDataKeySubKeyArr = explode(
					separator: ':',
					string: $activeRequestDataKeySubKey
				);
				$value = $this->httpObj->httpRequestObj->activeRequestData[$activeRequestDataKey];
				foreach ($activeRequestDataKeySubKeyArr as $_activeRequestDataKeySubKey) {
					if (!isset($value[$_activeRequestDataKeySubKey])) {
						$errorArr[] = "Missing hierarchy key '{$_activeRequestDataKeySubKey}' of '{$activeRequestDataKeySubKey}' in '{$activeRequestDataKey}'";
						continue;
					}
					$value = &$value[$_activeRequestDataKeySubKey];
				}
				$paramArr[$column] = $value;
				continue;
			} elseif ($activeRequestDataKey === 'sqlResults') {
				if (!isset($this->httpObj->httpRequestObj->activeRequestData[$activeRequestDataKey])) {
					$missExecution = true;
					continue;
				}
				$activeRequestDataKeySubKeyArr = explode(
					separator: ':',
					string: $activeRequestDataKeySubKey
				);
				$value = $this->httpObj->httpRequestObj->activeRequestData[$activeRequestDataKey];
				foreach ($activeRequestDataKeySubKeyArr as $_activeRequestDataKeySubKey) {
					if (!isset($value[$_activeRequestDataKeySubKey])) {
						$missExecution = true;
						continue;
					}
					$value = &$value[$_activeRequestDataKeySubKey];
				}
				$paramArr[$column] = $value;
				continue;
			} elseif ($activeRequestDataKey === 'custom') {
				$value = $activeRequestDataKeySubKey;
				$paramArr[$column] = $value;
				continue;
			} elseif ($activeRequestDataKey === 'variables') {
				if (isset($payloadVariableArr[$activeRequestDataKeySubKey])) {
					$paramArr[$column] = $payloadVariableArr[$activeRequestDataKeySubKey];
				} else {
					$errorArr[] = "Missing '{$activeRequestDataKey}' for '{$activeRequestDataKeySubKey}'";
				}
				continue;
			} elseif (isset($this->httpObj->httpRequestObj->activeRequestData[$activeRequestDataKey][$activeRequestDataKeySubKey])) {
				if (
					isset($this->httpObj->httpRequestObj->activeRequestData['requiredFieldArr'][$activeRequestDataKey])
					&& in_array(
						needle: $activeRequestDataKeySubKey,
						haystack: $this->httpObj->httpRequestObj->activeRequestData['requiredFieldArr'][$activeRequestDataKey],
						strict: Constant::$TRUE
					)
				) {
					if (isset($sqlParamConfig['dataType'])) {
						if (
							!DatabaseServerDataType::validateDataType(
								data: $this->httpObj->httpRequestObj->activeRequestData[$activeRequestDataKey][$activeRequestDataKeySubKey],
								dataType: $sqlParamConfig['dataType']
							)
						) {
							$errorArr[] = "Invalid required field data-type of '{$activeRequestDataKey}' for '{$activeRequestDataKeySubKey}'";
							continue;
						}
					}
				}
				$paramArr[$column] = $this->httpObj->httpRequestObj->activeRequestData[$activeRequestDataKey][$activeRequestDataKeySubKey];
				continue;
			} elseif (
				isset($this->httpObj->httpRequestObj->activeRequestData['requiredFieldArr'][$activeRequestDataKey])
				&& in_array(
					needle: $activeRequestDataKeySubKey,
					haystack: $this->httpObj->httpRequestObj->activeRequestData['requiredFieldArr'][$activeRequestDataKey],
					strict: Constant::$TRUE
				)
			) {
				$errorArr[] = "Missing required field '{$activeRequestDataKey}' for '{$activeRequestDataKeySubKey}'";
				continue;
			} else {
				$errorArr[] = "Invalid configuration of '{$activeRequestDataKey}' for '{$activeRequestDataKeySubKey}'";
				continue;
			}
		}

		return [$paramArr, $errorArr, $missExecution];
	}

	/**
	 * Function to find array is associative/simple array
	 * 
	 * @param array $arr Array to search for associative/simple array
	 * 
	 * @return bool
	 */
	private function isObject($arr): bool
	{
		$isObject = false;

		$index = 0;
		foreach ($arr as $key => &$value) {
			if ($key !== $index++) {
				$isObject = true;
				break;
			}
		}

		return $isObject;
	}

	/**
	 * Use results in where clause of sub queries recursively
	 * 
	 * @param array  $sqlConfig Sql config
	 * @param string $keyword   useHierarchy/useResultSet
	 * 
	 * @return bool
	 */
	private function getUseHierarchy(
		&$sqlConfig,
		$keyword = ''
	): bool {
		if (
			isset($sqlConfig[$keyword])
			&& $sqlConfig[$keyword] === Constant::$TRUE
		) {
			return true;
		}
		if (
			isset($sqlConfig['useHierarchy'])
			&& $sqlConfig['useHierarchy'] === Constant::$TRUE
		) {
			return true;
		}
		if (
			isset($sqlConfig['useResultSet'])
			&& $sqlConfig['useResultSet'] === Constant::$TRUE
		) {
			return true;
		}
		return false;
	}

	/**
	 * Return explain params recursively
	 * 
	 * @param array $sqlConfig   Sql config
	 * @param bool  $flag        useHierarchy/useResultSet flag
	 * @param bool  $isFirstCall Flag to check if this is first request
	 * 
	 * @return array
	 * @throws \Exception
	 */
	private function getExplainParam(
		&$sqlConfig,
		$flag,
		$isFirstCall
	): array {
		$explainParamArr = [];

		if (isset($sqlConfig['countQuery'])) {
			$sqlConfig['__CONFIG__'][] = [
				'column' => 'page',
				'activeRequestDataKey' => 'queryParamArr',
				'activeRequestDataKeySubKey' => 'page',
				'dataType' => DatabaseServerDataType::$INT,
				'isRequired' => Constant::$REQUIRED
			];
			$sqlConfig['__CONFIG__'][] = [
				'column' => 'perPage',
				'activeRequestDataKey' => 'queryParamArr',
				'activeRequestDataKeySubKey' => 'perPage',
				'dataType' => DatabaseServerDataType::$INT
			];

			foreach ($sqlConfig['__CONFIG__'] as $sqlParamConfig) {
				$activeRequestDataKey = $sqlParamConfig['activeRequestDataKey'];
				$activeRequestDataKeySubKey = $sqlParamConfig['activeRequestDataKeySubKey'];
				$dataType = isset($sqlParamConfig['dataType'])
					? $sqlParamConfig['dataType'] : DatabaseServerDataType::$Default;
				$isRequired = isset($sqlParamConfig['isRequired'])
					? $sqlParamConfig['isRequired'] : Constant::$FALSE;

				if (
					isset($explainParamArr[$activeRequestDataKeySubKey])
					&& $explainParamArr[$activeRequestDataKeySubKey]['isRequired'] === Constant::$TRUE
				) {
					continue;
				}
				$dataType['isRequired'] = $isRequired ? Constant::$TRUE : Constant::$FALSE;
				$explainParamArr[$activeRequestDataKeySubKey] = $dataType;
			}
		}

		foreach (['__PAYLOAD__', '__SET__', '__WHERE__'] as $option) {
			if (isset($sqlConfig[$option])) {
				foreach ($sqlConfig[$option] as $sqlParamConfig) {
					$activeRequestDataKey = $sqlParamConfig['activeRequestDataKey'];
					$activeRequestDataKeySubKey = $sqlParamConfig['activeRequestDataKeySubKey'];
					$dataType = isset($sqlParamConfig['dataType'])
						? $sqlParamConfig['dataType'] : DatabaseServerDataType::$Default;
					$isRequired = isset($sqlParamConfig['isRequired'])
						? $sqlParamConfig['isRequired'] : Constant::$FALSE;

					if ($activeRequestDataKey !== 'payload') {
						continue;
					}
					if (
						isset($explainParamArr[$activeRequestDataKeySubKey])
						&& $explainParamArr[$activeRequestDataKeySubKey]['isRequired'] === Constant::$TRUE
					) {
						continue;
					}
					$dataType['isRequired'] = $isRequired ? Constant::$TRUE : Constant::$FALSE;
					$explainParamArr[$activeRequestDataKeySubKey] = $dataType;
				}
			}
		}

		// Check for hierarchy
		$foundHierarchy = false;
		if (isset($sqlConfig['__WHERE__'])) {
			foreach ($sqlConfig['__WHERE__'] as $sqlParamConfig) {
				$activeRequestDataKey = $sqlParamConfig['activeRequestDataKey'];
				$activeRequestDataKeySubKey = $sqlParamConfig['activeRequestDataKeySubKey'];
				if (
					in_array(
						needle: $activeRequestDataKey,
						haystack: ['sqlResults', 'sqlParamArr', 'sqlPayload'],
						strict: Constant::$TRUE
					)
				) {
					$foundHierarchy = true;
					break;
				}
			}
			if (
				!$isFirstCall
				&& $flag
				&& !$foundHierarchy
			) {
				throw new \Exception(
					message: 'Invalid config: missing ' . $activeRequestDataKey,
					code: HttpStatus::$InternalServerError
				);
			}
		}

		// Check in subQuery//'__SUB-PAYLOAD__'
		foreach (['__SUB-PAYLOAD__', '__SUB-QUERY__'] as $option) {
			if (isset($sqlConfig[$option])) {
				foreach ($sqlConfig[$option] as $module => &$moduleSqlConfig) {
					$flag = ($flag) ?? $this->getUseHierarchy(
						sqlConfig: $moduleSqlConfig
					);
					$moduleExplainParamArr = $this->getExplainParam(
						sqlConfig: $moduleSqlConfig,
						flag: $flag,
						isFirstCall: Constant::$FALSE
					);
					if ($flag) {
						if (!empty($moduleExplainParamArr)) {
							$explainParamArr[$module] = $moduleExplainParamArr;
						}
					} else {
						foreach ($moduleExplainParamArr as $activeRequestDataKeySubKey => $field) {
							if (!isset($explainParamArr[$activeRequestDataKeySubKey])) {
								$explainParamArr[$activeRequestDataKeySubKey] = $field;
							}
						}
					}
				}
			}
		}

		return $explainParamArr;
	}

	/**
	 * Function to reset data for module key wise
	 * 
	 * @param string $activeRequestDataKey sqlResults / sqlParamArr / sqlPayload
	 * @param array  $payloadKeyArr              Module key's in recursion
	 * @param array  $record                     Record data fetched from DB
	 * 
	 * @return void
	 */
	private function resetFetchData(
		$activeRequestDataKey,
		$payloadKeyArr,
		$record
	): void {
		if (
			empty($payloadKeyArr)
			|| count(
				value: $payloadKeyArr
			) === 0
		) {
			$this->httpObj->httpRequestObj->activeRequestData[$activeRequestDataKey] = [];
			$this->httpObj->httpRequestObj->activeRequestData[$activeRequestDataKey]['return'] = [];
		}
		$httpReq = &$this->httpObj->httpRequestObj->activeRequestData[$activeRequestDataKey]['return'];
		if (!empty($payloadKeyArr)) {
			foreach ($payloadKeyArr as $moduleKey) {
				if (!isset($httpReq[$moduleKey])) {
					$httpReq[$moduleKey] = [];
				}
				$httpReq = &$httpReq[$moduleKey];
			}
		}
		$httpReq = $record;
	}

	/**
	 * Rate Limiting request on basis of Sql config
	 * 
	 * @param array $sqlConfig Sql config
	 * 
	 * @return void
	 * @throws \Exception
	 */
	private function rateLimitRoute(&$sqlConfig): void
	{
		if (
			$this->httpObj->httpRequestObj->isPublicRequest
			|| !CommonFunction::isEnabled(
				httpObj: $this->httpObj,
				feature: 'customer_enabled_rate_limiting_for_route'
			)
			|| !isset($sqlConfig['rateLimitMaxRequest'])
			|| !isset($sqlConfig['rateLimitMaxRequestWindow'])
		) {
			return;
		}

		$payloadSignature = [
			'httpRequestIp' => $this->httpObj->httpReqData['server']['httpRequestIp'],
			'customerId' => $this->httpObj->httpRequestObj->customerId,
			'httpMethod' => $this->httpObj->httpReqData['server']['httpMethod'],
			'Route' => $this->httpObj->httpReqData['get'][ROUTE_URL_PARAM],
		];
		if (isset($this->httpObj->httpRequestObj->activeRequestData['userData'])) {
			$payloadSignature['customerUserGroupId'] = ($this->httpObj->httpRequestObj->activeRequestData['userData']['customer_user_group_id'] !== Constant::$NULL
				? $this->httpObj->httpRequestObj->activeRequestData['userData']['customer_user_group_id'] : 0);
			$payloadSignature['customerUserId'] = ($this->httpObj->httpRequestObj->customerUserId !== Constant::$NULL
				? $this->httpObj->httpRequestObj->customerUserId : 0);
		}
		$hash = json_encode(
			value: $payloadSignature
		);
		$rateLimitKey = md5(
			string: $hash
		);

		// @throws \Exception
		$this->httpObj->httpRequestObj->rateLimiterObj->checkRateLimit(
			rateLimitPrefix: Env::$rateLimitRoutePrefix,
			rateLimitMaxRequest: $sqlConfig['rateLimitMaxRequest'],
			rateLimitMaxRequestWindow: $sqlConfig['rateLimitMaxRequestWindow'],
			rateLimitKey: $rateLimitKey
		);
	}

	/**
	 * Check Referrer Lag
	 * 
	 * @param array $sqlConfig Sql config
	 * 
	 * @return void
	 * @throws \Exception
	 */
	private function checkReferrerLag(&$sqlConfig): void
	{
		$customerUserId = 0;
		if (isset($this->httpObj->httpRequestObj->customerUserId)) {
			$customerUserId = $this->httpObj->httpRequestObj->customerUserId;
		}
		$customerUserReferrerLagKey = CacheServerKey::customerUserReferrerLag(
			customerId: $this->httpObj->httpRequestObj->customerId,
			customerUserId: $customerUserId
		);
		if (
			isset($sqlConfig['referrerLagWindow'])
			&& count(
				value: $sqlConfig['referrerLagWindow']
			) > 0
		) {
			if (
				!$this->httpObj->httpRequestObj->customerCacheObj->cacheExist(
					cacheKey: $customerUserReferrerLagKey
				)
			) {
				throw new \Exception(
					message: 'Referrer lag not initiated',
					code: HttpStatus::$BadRequest
				);
			}
			$referrerLagData = $this->httpObj->httpRequestObj->customerCacheObj->cacheGet(
				cacheKey: $customerUserReferrerLagKey
			);
			if (
				isset($referrerLagData['initRoute'])
				&& isset($referrerLagData['timestamp'])
			) {
				$found = false;
				foreach ($sqlConfig['referrerLagWindow'] as $referrerSqlConfig) {
					if ($referrerLagData['initRoute'] === $referrerSqlConfig['referrer']) {
						$tsDiff = Env::$timestamp - $referrerSqlConfig['timestamp'];
						if (
							isset($referrerSqlConfig['minimumReferrerLagWindow'])
							&& $tsDiff >= $referrerSqlConfig['minimumReferrerLagWindow']
						) {
							if (isset($referrerSqlConfig['maximumReferrerLagWindow'])) {
								if ($tsDiff <= $referrerSqlConfig['maximumReferrerLagWindow']) {
									$found = true;
								} else {
									$this->httpObj->httpRequestObj->customerCacheObj->cacheDelete(
										cacheKey: $customerUserReferrerLagKey
									);
								}
							} else {
								$found = true;
							}
						} else {
							$this->httpObj->httpRequestObj->customerCacheObj->cacheDelete(
								cacheKey: $customerUserReferrerLagKey
							);
						}
					}
				}
				if (!$found) {
					throw new \Exception(
						message: 'Referrer lag not configured',
						code: HttpStatus::$BadRequest
					);
				}
			}
		}

		if (
			isset($sqlConfig['enableReferrerLag'])
			&& $sqlConfig['enableReferrerLag'] === Constant::$YES
		) {
			if (
				!$this->httpObj->httpRequestObj->customerCacheObj->cacheExist(
					cacheKey: $customerUserReferrerLagKey
				)
			) {
				$this->httpObj->httpRequestObj->customerCacheObj->cacheSet(
					cacheKey: $customerUserReferrerLagKey,
					cacheValue: [
						'initRoute' => $this->httpObj->httpRequestObj->routeParserObj->configuredRoute,
						'timestamp' => Env::$timestamp
					]
				);
			} else {
				throw new \Exception(
					message: 'Referrer lag is enabled',
					code: HttpStatus::$BadRequest
				);
			}
		}
	}

	/**
	 * Check for Idempotent Window
	 * 
	 * @param array $sqlConfig       Sql config
	 * @param array $payloadArr Payload Indexes
	 * 
	 * @return array
	 */
	private function checkIdempotent(
		&$sqlConfig,
		$payloadArr
	): array {
		$idempotentWindow = 0;
		$hashKey = null;
		$hashJson = null;
		if (
			isset($sqlConfig['idempotentWindow'])
			&& is_numeric(
				value: $sqlConfig['idempotentWindow']
			)
			&& $sqlConfig['idempotentWindow'] > 0
		) {
			$idempotentWindow = (int)$sqlConfig['idempotentWindow'];
			if ($idempotentWindow) {
				$payloadSignature = [
					'idempotentSecret' => Env::$idempotentSecret,
					'idempotentWindow' => $idempotentWindow,
					'httpRequestIp' => $this->httpObj->httpReqData['server']['httpRequestIp'],
					'customerId' => $this->httpObj->httpRequestObj->customerId,
					'customerUserId' => $this->httpObj->httpRequestObj->customerUserId,
					'httpMethod' => $this->httpObj->httpReqData['server']['httpMethod'],
					'Route' => $this->httpObj->httpReqData['get'][ROUTE_URL_PARAM],
					'payload' => $this->httpObj->httpRequestObj->dataDecodeObj->get(
						keyString: implode(
							separator: ':', array: $payloadArr
						)
					)
				];
				if (isset($this->httpObj->httpRequestObj->activeRequestData['userData'])) {
					$payloadSignature['customerUserGroupId'] = ($this->httpObj->httpRequestObj->activeRequestData['userData']['customer_user_group_id'] !== Constant::$NULL
						? $this->httpObj->httpRequestObj->activeRequestData['userData']['customer_user_group_id'] : 0);
					$payloadSignature['customerUserId'] = ($this->httpObj->httpRequestObj->customerUserId !== Constant::$NULL
						? $this->httpObj->httpRequestObj->customerUserId : 0);
				}

				$hash = json_encode(
					value: $payloadSignature
				);
				$hashKey = md5(
					string: $hash
				);
				if (
					$this->httpObj->httpRequestObj->isPrivateRequest
					&& $this->httpObj->httpRequestObj->customerCacheObj->cacheExist(
						cacheKey: $hashKey
					)
				) {
					$hashJson = str_replace(
						search: 'JSON',
						replace: json_encode(
							value: $this->httpObj->httpRequestObj->customerCacheObj->cacheGet(
								cacheKey: $hashKey
							)
						),
						subject: '{"Idempotent": JSON, "Status": 200}'
					);
				}
			}
		}

		return [$idempotentWindow, $hashKey, $hashJson];
	}

	/**
	 * Lag response
	 * 
	 * @param array $sqlConfig Sql config
	 * 
	 * @return void
	 */
	private function lagResponse($sqlConfig): void
	{
		if (
			$this->httpObj->httpRequestObj->isPublicRequest
			|| !isset($sqlConfig['responseLag'])
		) {
			return;
		}

		$payloadSignature = [
			'httpRequestIp' => $this->httpObj->httpReqData['server']['httpRequestIp'],
			'customerId' => $this->httpObj->httpRequestObj->customerId,
			'httpMethod' => $this->httpObj->httpReqData['server']['httpMethod'],
			'Route' => $this->httpObj->httpReqData['get'][ROUTE_URL_PARAM],
		];
		if (isset($this->httpObj->httpRequestObj->activeRequestData['userData'])) {
			$payloadSignature['customerUserGroupId'] = ($this->httpObj->httpRequestObj->activeRequestData['userData']['customer_user_group_id'] !== Constant::$NULL
				? $this->httpObj->httpRequestObj->activeRequestData['userData']['customer_user_group_id'] : 0);
			$payloadSignature['customerUserId'] = ($this->httpObj->httpRequestObj->customerUserId !== Constant::$NULL
				? $this->httpObj->httpRequestObj->customerUserId : 0);
		}

		$hash = json_encode(
			value: $payloadSignature
		);
		$hashKey = 'LAG:' . md5(
			string: $hash
		);

		if (
			$this->httpObj->httpRequestObj->customerCacheObj->cacheExist(
				cacheKey: $hashKey
			)
		) {
			$noOfRequest = $this->httpObj->httpRequestObj->customerCacheObj->cacheGet(
				cacheKey: $hashKey
			);
		} else {
			$noOfRequest = 0;
		}

		$this->httpObj->httpRequestObj->customerCacheObj->cacheSet(
			cacheKey: $hashKey,
			cacheValue: ++$noOfRequest,
			cacheExpire: 3600
		);

		$lag = 0;
		$responseLag = &$sqlConfig['responseLag'];
		if (
			is_array(
				value: $responseLag
			)
		) {
			foreach ($responseLag as $start => $newLag) {
				if ($noOfRequest > $start) {
					$lag = $newLag;
				}
			}
		}

		if ($lag > 0) {
			sleep(
				seconds: $lag
			);
		}
	}

	/**
	 * Get Trigger data
	 * 
	 * @param array $triggerConfig Trigger Config
	 * 
	 * @return mixed
	 */
	public function getTriggerData($triggerConfig): mixed
	{
		if (!isset($this->httpObj->httpRequestObj->activeRequestData['authId'])) {
			throw new \Exception(
				message: 'Missing token',
				code: HttpStatus::$InternalServerError
			);
		}

		$httpReqData = [];

		$isObject = (!isset($triggerConfig[0])) ? Constant::$TRUE : Constant::$FALSE;
		if (
			!$isObject
			&& isset($triggerConfig[0])
			&& count(
				value: $triggerConfig
			) === 1
		) {
			$triggerConfig = $triggerConfig[0];
			$isObject = true;
		}

		$triggerOutput = [];
		if ($isObject) {
			$httpReqData = $this->getTriggerHttp(
				triggerConfig: $triggerConfig
			);
			[$responseHeaderArr, $responseContent, $responseCode] = Start::http(
				httpReqData: $httpReqData
			);
			$triggerOutput = &$responseContent;
		} else {
			$iTriggerCount = count(
				value: $triggerConfig
			);
			for ($iTrigger = 0; $iTrigger < $iTriggerCount; $iTrigger++) {
				$httpReqData = $this->getTriggerHttp(
					triggerConfig: $triggerConfig[$iTrigger]
				);
				[$responseHeaderArr, $responseContent, $responseCode] = Start::http(
					httpReqData: $httpReqData
				);
				$triggerOutput[] = &$responseContent;
			}
		}

		return $triggerOutput;
	}

	/**
	 * Get Trigger detail
	 * 
	 * @param array $triggerConfig Trigger Config
	 * 
	 * @return mixed
	 */
	public function getTriggerHttp($triggerConfig)
	{
		$method = $triggerConfig['__METHOD__'];
		[$routeElementArrArr, $errorArr] = $this->getTriggerParam(
			payloadConfig: $triggerConfig['__ROUTE__']
		);

		if ($errorArr) {
			return $errorArr;
		}

		$route = '/' . implode(
			separator: '/', array: $routeElementArrArr
		);

		$queryStringArr = [];
		$payloadArr = [];

		if (isset($triggerConfig['__QUERY-STRING__'])) {
			[$queryStringArr, $errorArr] = $this->getTriggerParam(
				payloadConfig: $triggerConfig['__QUERY-STRING__']
			);

			if ($errorArr) {
				return $errorArr;
			}
		}
		if (isset($triggerConfig['__PAYLOAD__'])) {
			[$payloadArr, $errorArr] = $this->getTriggerParam(
				payloadConfig: $triggerConfig['__PAYLOAD__']
			);
			if ($errorArr) {
				return $errorArr;
			}
		}

		$httpReqData['streamData'] = false;
		$httpReqData['server']['domainName'] = $this->httpObj->httpReqData['server']['domainName'];
		$httpReqData['server']['httpMethod'] = $method;
		$httpReqData['server']['httpRequestIp'] = $this->httpObj->httpReqData['server']['httpRequestIp'];
		$httpReqData['header'] = $this->httpObj->httpReqData['header'];
		$httpReqData['post'] = json_encode(
			value: $payloadArr
		);
		$httpReqData['get'] = $queryStringArr;
		$httpReqData['get'][ROUTE_URL_PARAM] = $route;
		$httpReqData['isWebRequest'] = false;

		return $httpReqData;
	}

	/**
	 * Get Trigger param's
	 * 
	 * @param array $payloadConfig      API Payload configuration
	 * @param array $payloadVariableArr Payload Variables
	 * 
	 * @return array
	 * @throws \Exception
	 */
	private function getTriggerParam(&$payloadConfig): array
	{
		$paramArr = [];
		$errorArr = [];

		// Collect param values as per config respectively
		foreach ($payloadConfig as &$payloadParamConfig) {
			$column = $payloadParamConfig['column'] ?? null;

			$activeRequestDataKey = $payloadParamConfig['activeRequestDataKey'];
			$activeRequestDataKeySubKey = $payloadParamConfig['activeRequestDataKeySubKey'];
			if ($activeRequestDataKey === 'function') {
				$function = $activeRequestDataKeySubKey;
				$value = $function($this->httpObj->httpRequestObj->activeRequestData);
				if ($column === Constant::$NULL) {
					$paramArr[] = $value;
				} else {
					$paramArr[$column] = $value;
				}
				continue;
			} elseif (
				in_array(
					needle: $activeRequestDataKey,
					haystack: ['sqlResults', 'sqlParamArr', 'sqlPayload'],
					strict: Constant::$TRUE
				)
			) {
				$activeRequestDataKeySubKeyArr = explode(
					separator: ':', string: $activeRequestDataKeySubKey
				);
				$value = $this->httpObj->httpRequestObj->activeRequestData[$activeRequestDataKey];
				foreach ($activeRequestDataKeySubKeyArr as $_activeRequestDataKeySubKey) {
					if (!isset($value[$_activeRequestDataKeySubKey])) {
						throw new \Exception(
							message: 'Invalid hierarchy:  Missing hierarchy data',
							code: HttpStatus::$InternalServerError
						);
					}
					$value = $value[$_activeRequestDataKeySubKey];
				}
				if ($column === Constant::$NULL) {
					$paramArr[] = $value;
				} else {
					$paramArr[$column] = $value;
				}
				continue;
			} elseif ($activeRequestDataKey === 'custom') {
				$value = $activeRequestDataKeySubKey;
				if ($column === Constant::$NULL) {
					$paramArr[] = $value;
				} else {
					$paramArr[$column] = $value;
				}
				continue;
			} elseif (isset($this->httpObj->httpRequestObj->activeRequestData[$activeRequestDataKey][$activeRequestDataKeySubKey])) {
				$value = $this->httpObj->httpRequestObj->activeRequestData[$activeRequestDataKey][$activeRequestDataKeySubKey];
				if ($column === Constant::$NULL) {
					$paramArr[] = $value;
				} else {
					$paramArr[$column] = $value;
				}
				continue;
			} else {
				$errorArr[] = "Invalid configuration of '{$activeRequestDataKey}' for '{$activeRequestDataKeySubKey}'";
				continue;
			}
		}

		return [$paramArr, $errorArr];
	}

	/**
	 * Process import function of configuration
	 * 
	 * @param array $sqlConfig    Sql config
	 * @param bool  $useHierarchy If true - Uses parent payload/results in child
	 * 
	 * @return string
	 */
	private function generateImportSampleCsv(
		&$sqlConfig,
		$useHierarchy
	): string {
		$explainParamArr = $this->getExplainParam(
			sqlConfig: $sqlConfig,
			flag: $useHierarchy,
			isFirstCall: Constant::$TRUE
		);
		$paramArr = $this->genCsvHelper(
			headerCsv: 'CSV',
			explainParamArr: $explainParamArr
		);

		$header = [];
		$header[] = '__mode__';
		foreach ($paramArr as $r => $p) {
			if (
				is_array(
					value: $p
				)
			) {
				$indexCount = count(
					value: $p
				);
				for ($index = 0; $index < $indexCount; $index++) {
					$header[] = $p[$index];
				}
			} else {
				$header[] = $p;
			}
		}
		$csv = '"' . implode(
			separator: '","',
			array: $header
		) . '"' . PHP_EOL;
		$blankStr = '';
		foreach ($paramArr as $r => $p) {
			if ($r === 'CSV') {
				$indexCount = count(
					value: $header
				);
				for ($index = 1; $index < $indexCount; $index++) {
					$blankStr = ',""';
				}
			}
			$csv .= "{$r}{$blankStr}" . PHP_EOL;
		}

		$filename = date('Ymd-His') . '-import-sample.csv';
		$headerArr = [];
		// Export header
		$headerArr['Content-type'] = 'text/csv';
		$headerArr['Content-Disposition'] = "attachment; filename={$filename}";
		$headerArr['Pragma'] = 'no-cache';
		$headerArr['Expires'] = '0';

		return [$headerArr, $csv, HttpStatus::$Ok];
	}

	/**
	 * Generate sample CSV helper
	 * 
	 * @param string $module
	 * @param array  $explainParamArr
	 * 
	 * @return array
	 */
	private function genCsvHelper(
		$module,
		$explainParamArr
	): array {
		$headerCsvArr = [];
		foreach ($explainParamArr as $explainParamKey => &$explainParamKeyValue) {
			if (isset($explainParamKeyValue['dataType'])) {
				$columnHeader = "{$module}:{$explainParamKey}";
				$headerCsvArr[$module][] = $columnHeader;
			} else {
				$_module = "{$module}:{$explainParamKey}";
				$headerArr = $this->genCsvHelper(
					module: $_module,
					explainParamArr: $explainParamKeyValue
				);
				foreach ($headerArr as $headerKey => &$headerKeyValue) {
					$headerCsvArr[$headerKey] = $headerKeyValue;
				}
			}
		}

		return $headerCsvArr;
	}

	/**
	 * Basic Read Processes for process Function
	 * 
	 * @param array $sqlConfig    Sql config
	 * @param bool  $useResultSet If true - Uses parent payload/results in child
	 * 
	 * @return array
	 */
	private function readBasics(
		&$sqlConfig,
		&$useResultSet
	) {
		// Load Sql
		$sqlConfig = $this->httpObj->httpRequestObj->routeParserObj->sqlConfig;

		// Rate Limiting request if configured for Route Sql.
		$this->rateLimitRoute(
			sqlConfig: $sqlConfig
		);

		// Check for configured referrer Lags
		$this->checkReferrerLag(
			sqlConfig: $sqlConfig
		);

		// Use results in where clause of sub queries recursively
		$useResultSet = $this->getUseHierarchy(
			sqlConfig: $sqlConfig,
			keyword: 'useResultSet'
		);

		if (
			$this->httpObj->httpRequestObj->routeParserObj->routeEndingWithReservedKeywordFlag
			&& $this->httpObj->httpRequestObj->routeParserObj->routeEndingReservedKeyword === Env::$explainRequestRouteKeyword
			&& CommonFunction::isEnabled(
				httpObj: $this->httpObj,
				feature: 'customer_enabled_explain_request'
			)
		) {
			return $this->explain(
				sqlConfig: $sqlConfig,
				flag: $useResultSet
			);
		}

		return false;
	}

	/**
	 * Basic Write Processes for process Function (Supplement is considered as Write)
	 * 
	 * @param array $sqlConfig    Sql config
	 * @param bool  $useHierarchy If true - Uses parent payload/results in child
	 * 
	 * @return bool
	 */
	private function writeBasics(
		&$sqlConfig,
		&$useHierarchy
	): bool {
		// Load Sql
		$sqlConfig = $this->httpObj->httpRequestObj->routeParserObj->sqlConfig;

		// Lag response
		$this->lagResponse(
			sqlConfig: $sqlConfig
		);

		// Check for configured referrer Lags
		$this->checkReferrerLag(
			sqlConfig: $sqlConfig
		);

		// Rate Limiting request if configured for Route Sql.
		$this->rateLimitRoute(
			sqlConfig: $sqlConfig
		);

		// Use results in where clause of sub queries recursively
		$useHierarchy = $this->getUseHierarchy(
			sqlConfig: $sqlConfig,
			keyword: 'useHierarchy'
		);

		if (
			$this->httpObj->httpRequestObj->routeParserObj->routeEndingWithReservedKeywordFlag
			&& $this->httpObj->httpRequestObj->routeParserObj->routeEndingReservedKeyword === Env::$explainRequestRouteKeyword
			&& CommonFunction::isEnabled(
				httpObj: $this->httpObj,
				feature: 'customer_enabled_explain_request'
			)
		) {
			return $this->explain(
				sqlConfig: $sqlConfig,
				flag: $useHierarchy
			);
		}

		if (
			$this->httpObj->httpRequestObj->routeParserObj->routeEndingWithReservedKeywordFlag
			&& $this->httpObj->httpRequestObj->routeParserObj->routeEndingReservedKeyword === Env::$importSampleRequestRouteKeyword
		) {
			return $this->generateImportSampleCsv(
				sqlConfig: $sqlConfig,
				useHierarchy: $useHierarchy
			);
		}

		return false;
	}

	/**
	 * Get results to be cached flag
	 * 
	 * @param array $sqlConfig Sql config
	 * 
	 * @return bool
	 */
	private function getToBeCached(
		$sqlConfig
	): bool {
		$toBeCached = false;
		if (
			CommonFunction::isEnabled(
				httpObj: $this->httpObj,
				feature: 'customer_enabled_response_caching'
			)
			&& isset($sqlConfig['queryCacheKey'])
			&& !isset($this->httpObj->httpRequestObj->activeRequestData['queryParamArr']['orderBy'])
		) {
			$cacheReqCount = 0;
			$queryCacheReqFlag = false;
			for ($index = 0;$index < 5; $index++) {
				$json = $this->httpObj->httpRequestObj->customerQueryCacheObj->queryCacheGet(
					customerId: $this->httpObj->httpRequestObj->customerId,
					queryCacheKey: $sqlConfig['queryCacheKey']
				);
				if ($json !== Constant::$NULL) {
					$cacheHit = 'true';
					$this->httpObj->httpResponseObj->dataEncodeObj->appendKeyData(
						objectKey: 'cacheHit',
						data: $cacheHit
					);
					$this->httpObj->httpResponseObj->dataEncodeObj->appendData(
						data: $json
					);
					return true;
				} else {
					if (!$queryCacheReqFlag) {
						$cacheReqCount = $this->httpObj->httpRequestObj->customerQueryCacheObj->queryCacheIncrement(
							customerId: $this->httpObj->httpRequestObj->customerId,
							queryCacheKey: $sqlConfig['queryCacheKey']
						);
						if ($cacheReqCount === 1) {
							$toBeCached = true;
							break;
						} else {
							$queryCacheReqFlag = true;
						}
					}
					if ($queryCacheReqFlag) {
						sleep(1);
					}
				}
			}
			if (
				$queryCacheReqFlag
				&& $cacheReqCount > 1
			) {
				throw new \Exception(
					message: 'Invalid query cache request flag',
					code: HttpStatus::$InternalServerError
				);
			}
		}

		return $toBeCached;
	}


	/**
	 * Explain configuration
	 * 
	 * @param array $sqlConfig Sql config
	 * @param bool  $flag      If true - Uses parent payload/results in child
	 * 
	 * @return bool
	 */
	private function explain(
		&$sqlConfig,
		$flag
	): bool {
		$this->dataEncodeObj->startObject(
			objectKey: 'Config'
		);
		$this->dataEncodeObj->addKeyData(
			objectKey: 'Route',
			data: $this->httpObj->httpRequestObj->routeParserObj->configuredRoute
		);
		$this->dataEncodeObj->addKeyData(
			objectKey: 'Payload',
			data: $this->getExplainParam(
				sqlConfig: $sqlConfig,
				flag: $flag,
				isFirstCall: Constant::$TRUE
			)
		);
		$this->dataEncodeObj->endObject();

		return true;
	}
}
