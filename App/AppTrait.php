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

use Microservices\App\Start;
use Microservices\App\CacheServerKey;
use Microservices\App\Counter;
use Microservices\App\Constant;
use Microservices\App\DatabaseServerDataType;
use Microservices\App\DbCommonFunction;
use Microservices\App\Env;
use Microservices\App\HttpStatus;
use Microservices\App\RateLimiter;
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
	 * Rate Limiter
	 *
	 * @var null|RateLimiter
	 */
	private $rateLimiter = null;

	/**
	 * Validator class object
	 *
	 * @var null|Validator
	 */
	public $validator = null;

	/**
	 * Function to help execute PHP functions enclosed with double quotes
	 *
	 * @param mixed $param Returned values by PHP inbuilt functions
	 *
	 * @return mixed
	 */
	public function execPhpFunc($param): mixed
	{
		return $param;
	}

	/**
	 * Sets required payload
	 *
	 * @param array $sqlConfig   Config from file
	 * @param bool  $isFirstCall true to represent the first call in recursion
	 * @param bool  $flag        useHierarchy / useResultSet flag
	 *
	 * @return array
	 * @throws \Exception
	 */
	private function getRequired(&$sqlConfig, $isFirstCall, $flag): array
	{
		$requiredFields = [];

		foreach (['__PAYLOAD__', '__SET__', '__WHERE__'] as $option) {
			if (isset($sqlConfig[$option])) {
				foreach ($sqlConfig[$option] as $sqlParamConfig) {
					if ($fetchFrom === 'function') {
						continue;
					}
					$isRequired = isset($sqlParamConfig['isRequired'])
						? $sqlParamConfig['isRequired'] : false;

					if ($isRequired) {
						$fetchFrom = $sqlParamConfig['fetchFrom'];
						$fetchFromDetails = $sqlParamConfig['fetchFromDetails'];

						if (!isset($requiredFields[$fetchFrom])) {
							$requiredFields[$fetchFrom] = [];
						}
						if (!in_array($fetchFromDetails, $requiredFields[$fetchFrom])) {
							$requiredFields[$fetchFrom][] = $fetchFromDetails;
						}
					}
				}
			}
		}

		// Check for hierarchy setting
		$foundHierarchy = false;
		if (isset($sqlConfig['__WHERE__'])) {
			foreach ($sqlConfig['__WHERE__'] as $sqlParamConfig) {
				$fetchFrom = $sqlParamConfig['fetchFrom'];
				$fetchFromDetails = $sqlParamConfig['fetchFromDetails'];

				if (
					$isFirstCall
					&& in_array(
						needle: $fetchFrom,
						haystack: ['sqlResults', 'sqlParams', 'sqlPayload']
					)
				) {
					throw new \Exception(
						message: "First query can not have {$fetchFrom} config",
						code: HttpStatus::$InternalServerError
					);
				}
				if (
					in_array(
						needle: $fetchFrom,
						haystack: ['sqlResults', 'sqlParams', 'sqlPayload']
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
			//          message: 'Invalid config: missing ' . $fetchFrom,
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
				&& !$this->isObject($sqlConfig['__SUB-QUERY__'])
			) {
				throw new \Exception(
					message: 'Sub-Query should be an associative array',
					code: HttpStatus::$InternalServerError
				);
			}
			if (
				isset($sqlConfig['__SUB-PAYLOAD__'])
				&& !$this->isObject($sqlConfig['__SUB-PAYLOAD__'])
			) {
				throw new \Exception(
					message: 'Sub-Payload should be an associative array',
					code: HttpStatus::$InternalServerError
				);
			}
			foreach (['__SUB-QUERY__', '__SUB-PAYLOAD__'] as $option) {
				if (isset($sqlConfig[$option])) {
					foreach ($sqlConfig[$option] as $module => &$moduleSqlConfig) {
						$flag = ($flag) ?? $this->getUseHierarchy($moduleSqlConfig);
						$moduleRequiredFields = $this->getRequired(
							$moduleSqlConfig,
							false,
							$flag
						);
						if ($flag) {
							$requiredFields[$module] = $moduleRequiredFields;
						} else {
							foreach ($moduleRequiredFields as $fetchFrom => &$fetchFromDetailsArr) {
								if (!isset($requiredFields[$fetchFrom])) {
									$requiredFields[$fetchFrom] = [];
								}
								foreach ($fetchFromDetailsArr as $fetchFromDetails) {
									if (!in_array($fetchFromDetails, $requiredFields[$fetchFrom])) {
										$requiredFields[$fetchFrom][] = $fetchFromDetails;
									}
								}
							}
						}
					}
				}
			}
		}

		return $requiredFields;
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
		if ($this->validator === null) {
			$this->validator = new Validator($this->http);
		}

		return $this->validator->validate(validationConfig: $validationConfig);
	}

	/**
	 * Returns Query and Params for execution
	 *
	 * @param array      $sqlDetails  Config from file
	 * @param array|null $configKeys  Config Keys
	 *
	 * @return array
	 */
	private function getSqlAndParamsNamedMode(
		&$sqlDetails,
		$configKeys = null
	): array {
		$id = null;
		$sql = '';
		/*!999999 comment goes here */
		if (isset($sqlDetails['__SQL-COMMENT__'])) {
			$sql .= '/' . '*!999999 ';
			$sql .= $sqlDetails['__SQL-COMMENT__'];
			$sql .= ' */';
		}
		switch (true) {
			case isset($sqlDetails['__QUERY__']):
				$sql .= $sqlDetails['__QUERY__'];
				break;
			case isset($sqlDetails['__DOWNLOAD__']):
				$sql .= $sqlDetails['__DOWNLOAD__'];
				break;
		}
		$sqlParams = [];
		$paramKeys = [];
		$errors = [];
		$row = [];
		$__SET__ = [];

		$missExecution = $wMissExecution = false;
		// Check __SET__
		if (
			isset($sqlDetails['__SET__'])
			&& count(value: $sqlDetails['__SET__']) !== 0
		) {
			$payloadVariables = $sqlDetails['__VARIABLES__'] ?? [];
			[$params, $errors, $missExecution] = $this->getSqlParams(
				$sqlDetails['__SET__'],
				$payloadVariables
			);
			if (
				empty($errors)
				&& !$missExecution
			) {
				if (!empty($params)) {
					// __SET__ not compulsory in query
					$found = strpos(haystack: $sql, needle: '__SET__') !== false;
					foreach ($params as $param => &$v) {
						$param = str_replace(
							search: ['`', ' '],
							replace: '',
							subject: $param
						);
						$paramKeys[] = $param;
						if ($found) {
							$__SET__[] = "`{$param}` = :{$param}";
						}
						$sqlParams[":{$param}"] = $v;
						$row[$param] = $v;
					}
				}
			}
		}

		// Check __WHERE__
		if (
			empty($errors)
			&& !$missExecution
			&& isset($sqlDetails['__WHERE__'])
			&& count(value: $sqlDetails['__WHERE__']) !== 0
		) {
			$wErrors = [];
			$payloadVariables = $sqlDetails['__VARIABLES__'] ?? [];
			[$sqlWhereParams, $wErrors, $wMissExecution] = $this->getSqlParams(
				$sqlDetails['__WHERE__'],
				$payloadVariables
			);
			if (
				empty($wErrors)
				&& !$wMissExecution
			) {
				if (!empty($sqlWhereParams)) {
					// __WHERE__ not compulsory in query
					$wfound = strpos(haystack: $sql, needle: '__WHERE__') !== false;
					if ($wfound) {
						$__WHERE__ = [];
						foreach ($sqlWhereParams as $param => &$v) {
							$wparam = $param = str_replace(
								search: ['`', ' '],
								replace: '',
								subject: $param
							);
							$i = 0;
							while (in_array(needle: $wparam, haystack: $paramKeys)) {
								$i++;
								$wparam = "{$param}{$i}";
							}
							$paramKeys[] = $wparam;
							$__WHERE__[] = "`{$param}` = :{$wparam}";
							$sqlParams[":{$wparam}"] = $v;
							$row[$wparam] = $v;
						}
						$sql = str_replace(
							search: '__WHERE__',
							replace: implode(separator: ' AND ', array: $__WHERE__),
							subject: $sql
						);
					}
				}
			} else {
				$errors = array_merge($errors, $wErrors);
			}
		}
		if (!empty($__SET__)) {
			$sql = str_replace(
				search: '__SET__',
				replace: implode(separator: ', ', array: $__SET__),
				subject: $sql
			);
		}

		if (!empty($row)) {
			$this->resetFetchData('sqlParams', $configKeys, $row);
		}

		return [$id, $sql, $sqlParams, $errors, ($missExecution || $wMissExecution)];
	}

	/**
	 * Returns Query and Params for execution
	 *
	 * @param array      $sqlDetails  Config from file
	 * @param array|null $configKeys  Config Keys
	 *
	 * @return array
	 */
	private function getSqlAndParamsUnnamedMode(
		&$sqlDetails,
		$configKeys = null
	): array {
		$id = null;
		$sql = '';
		/*!999999 comment goes here */
		if (isset($sqlDetails['__SQL-COMMENT__'])) {
			$sql .= '/' . '*!999999 ';
			$sql .= $sqlDetails['__SQL-COMMENT__'];
			$sql .= ' */';
		}
		switch (true) {
			case isset($sqlDetails['__QUERY__']):
				$sql .= $sqlDetails['__QUERY__'];
				break;
			case isset($sqlDetails['__DOWNLOAD__']):
				$sql .= $sqlDetails['__DOWNLOAD__'];
				break;
		}
		$sqlParams = [];
		$paramKeys = [];
		$errors = [];
		$row = [];
		$__SET__ = [];

		$missExecution = $wMissExecution = false;
		// Check __SET__
		if (
			isset($sqlDetails['__SET__'])
			&& count(value: $sqlDetails['__SET__']) !== 0
		) {
			$payloadVariables = $sqlDetails['__VARIABLES__'] ?? [];
			[$params, $errors, $missExecution] = $this->getSqlParams(
				$sqlDetails['__SET__'],
				$payloadVariables
			);
			if (
				empty($errors)
				&& !$missExecution
			) {
				if (!empty($params)) {
					// __SET__ not compulsory in query
					$found = strpos(haystack: $sql, needle: '__SET__') !== false;
					foreach ($params as $param => &$v) {
						$paramKeys[] = $param;
						if ($found) {
							$__SET__[] = "{$param} = ?";
						}
						$sqlParams[] = $v;
						$row[$param] = $v;
					}
				}
			}
		}

		// Check __WHERE__
		if (
			empty($errors)
			&& !$missExecution
			&& isset($sqlDetails['__WHERE__'])
			&& count(value: $sqlDetails['__WHERE__']) !== 0
		) {
			$wErrors = [];
			$payloadVariables = $sqlDetails['__VARIABLES__'] ?? [];
			[$sqlWhereParams, $wErrors, $wMissExecution] = $this->getSqlParams(
				$sqlDetails['__WHERE__'],
				$payloadVariables
			);
			if (
				empty($wErrors)
				&& !$wMissExecution
			) {
				if (!empty($sqlWhereParams)) {
					// __WHERE__ not compulsory in query
					$wfound = strpos(haystack: $sql, needle: '__WHERE__') !== false;
					if ($wfound) {
						$__WHERE__ = [];
						foreach ($sqlWhereParams as $param => &$v) {
							$wparam = $param;
							$i = 0;
							while (in_array(needle: $wparam, haystack: $paramKeys)) {
								$i++;
								$wparam = "{$param}{$i}";
							}
							$paramKeys[] = $wparam;
							$__WHERE__[] = "{$param} = ?";
							$sqlParams[] = $v;
							$row[$wparam] = $v;
						}
						$sql = str_replace(
							search: '__WHERE__',
							replace: implode(separator: ' AND ', array: $__WHERE__),
							subject: $sql
						);
					}
				}
			} else {
				$errors = array_merge($errors, $wErrors);
			}
		}
		if (!empty($__SET__)) {
			$sql = str_replace(
				search: '__SET__',
				replace: implode(separator: ', ', array: $__SET__),
				subject: $sql
			);
		}

		if (!empty($row)) {
			$this->resetFetchData('sqlParams', $configKeys, $row);
		}

		return [$id, $sql, $sqlParams, $errors, ($missExecution || $wMissExecution)];
	}

	/**
	 * Generates Params for statement to execute
	 *
	 * @param array $sqlConfig        Config from file
	 * @param array $payloadVariables Payload Variables
	 *
	 * @return array
	 * @throws \Exception
	 */
	private function getSqlParams(&$sqlConfig, &$payloadVariables): array
	{
		$missExecution = false;
		$sqlParams = [];
		$errors = [];

		// Collect param values as per config respectively
		foreach ($sqlConfig as $sqlParamConfig) {
			$var = $sqlParamConfig['column'];
			$fetchFrom = $sqlParamConfig['fetchFrom'];
			$fetchFromDetails = $sqlParamConfig['fetchFromDetails'];
			if ($fetchFrom === 'function') {
				$function = $fetchFromDetails;
				$value = $function($this->http->req->s);
				$sqlParams[$var] = $value;
				continue;
			} elseif (
				in_array(
					needle: $fetchFrom,
					haystack: ['sqlParams', 'sqlPayload']
				)
			) {
				if (!isset($this->http->req->s[$fetchFrom])) {
					$errors[] = "Missing key '{$fetchFromDetails}' in '{$fetchFrom}'";
					continue;
				}
				$fetchFromKeys = explode(separator: ':', string: $fetchFromDetails);
				$value = $this->http->req->s[$fetchFrom];
				foreach ($fetchFromKeys as $key) {
					if (!isset($value[$key])) {
						$errors[] = "Missing hierarchy key '{$key}' of '{$fetchFromDetails}' in '{$fetchFrom}'";
						continue;
					}
					$value = $value[$key];
				}
				$sqlParams[$var] = $value;
				continue;
			} elseif ($fetchFrom === 'sqlResults') {
				if (!isset($this->http->req->s[$fetchFrom])) {
					$missExecution = true;
					continue;
				}
				$fetchFromKeys = explode(separator: ':', string: $fetchFromDetails);
				$value = $this->http->req->s[$fetchFrom];
				foreach ($fetchFromKeys as $key) {
					if (!isset($value[$key])) {
						$missExecution = true;
						continue;
					}
					$value = $value[$key];
				}
				$sqlParams[$var] = $value;
				continue;
			} elseif ($fetchFrom === 'custom') {
				$value = $fetchFromDetails;
				$sqlParams[$var] = $value;
				continue;
			} elseif ($fetchFrom === 'variables') {
				if (isset($payloadVariables[$fetchFromDetails])) {
					$sqlParams[$var] = $payloadVariables[$fetchFromDetails];
				} else {
					$errors[] = "Missing '{$fetchFrom}' for '{$fetchFromDetails}'";
				}
				continue;
			} elseif (isset($this->http->req->s[$fetchFrom][$fetchFromDetails])) {
				if (in_array($fetchFromDetails, $this->http->req->s['requiredFields'][$fetchFrom])) {
					if (isset($sqlParamConfig['dataType'])) {
						if (
							!DatabaseServerDataType::validateDataType(
								data: $this->http->req->s[$fetchFrom][$fetchFromDetails],
								dataType: $sqlParamConfig['dataType']
							)
						) {
							$errors[] = "Invalid required field data-type of '{$fetchFrom}' for '{$fetchFromDetails}'";
							continue;
						}
					}
				}
				$sqlParams[$var] = $this->http->req->s[$fetchFrom][$fetchFromDetails];
				continue;
			} elseif (in_array($fetchFromDetails, $this->http->req->s['requiredFields'][$fetchFrom])) {
				$errors[] = "Missing required field '{$fetchFrom}' for '{$fetchFromDetails}'";
				continue;
			} else {
				$errors[] = "Invalid configuration of '{$fetchFrom}' for '{$fetchFromDetails}'";
				continue;
			}
		}

		return [$sqlParams, $errors, $missExecution];
	}

	/**
	 * Function to find wether provided array is associative/simple array
	 *
	 * @param array $arr Array to search for associative/simple array
	 *
	 * @return bool
	 */
	private function isObject($arr): bool
	{
		$isAssoc = false;

		$i = 0;
		foreach ($arr as $k => &$v) {
			if ($k !== $i++) {
				$isAssoc = true;
				break;
			}
		}

		return $isAssoc;
	}

	/**
	 * Use results in where clause of sub queries recursively
	 *
	 * @param array  $sqlConfig Config from file
	 * @param string $keyword   useHierarchy/useResultSet
	 *
	 * @return bool
	 */
	private function getUseHierarchy(&$sqlConfig, $keyword = ''): bool
	{
		if (
			isset($sqlConfig[$keyword])
			&& $sqlConfig[$keyword] === true
		) {
			return true;
		}
		if (
			isset($sqlConfig['useHierarchy'])
			&& $sqlConfig['useHierarchy'] === true
		) {
			return true;
		}
		if (
			isset($sqlConfig['useResultSet'])
			&& $sqlConfig['useResultSet'] === true
		) {
			return true;
		}
		return false;
	}

	/**
	 * Return explain params recursively
	 *
	 * @param array $sqlConfig   Config from file
	 * @param bool  $isFirstCall Flag to check if this is first request
	 * @param bool  $flag        useHierarchy/useResultSet flag
	 *
	 * @return array
	 * @throws \Exception
	 */
	private function getExplainParams(&$sqlConfig, $isFirstCall, $flag): array
	{
		$explainParams = [];

		if (isset($sqlConfig['countQuery'])) {
			$sqlConfig['__CONFIG__'][] = [
				'column' => 'page',
				'fetchFrom' => 'queryParams',
				'fetchFromDetails' => 'page',
				'dataType' => DatabaseServerDataType::$INT,
				'isRequired' => Constant::$REQUIRED
			];
			$sqlConfig['__CONFIG__'][] = [
				'column' => 'perPage',
				'fetchFrom' => 'queryParams',
				'fetchFromDetails' => 'perPage',
				'dataType' => DatabaseServerDataType::$INT
			];

			foreach ($sqlConfig['__CONFIG__'] as $sqlParamConfig) {
				$fetchFrom = $sqlParamConfig['fetchFrom'];
				$fetchFromDetails = $sqlParamConfig['fetchFromDetails'];
				$dataType = isset($sqlParamConfig['dataType'])
					? $sqlParamConfig['dataType'] : DatabaseServerDataType::$Default;
				$isRequired = isset($sqlParamConfig['isRequired'])
					? $sqlParamConfig['isRequired'] : false;

				if (
					isset($explainParams[$fetchFromDetails])
					&& $explainParams[$fetchFromDetails]['isRequired'] === true
				) {
					continue;
				}
				$dataType['isRequired'] = $isRequired ? true : false;
				$explainParams[$fetchFromDetails] = $dataType;
			}
		}

		foreach (['__PAYLOAD__', '__SET__', '__WHERE__'] as $option) {
			if (isset($sqlConfig[$option])) {
				foreach ($sqlConfig[$option] as $sqlParamConfig) {
					$fetchFrom = $sqlParamConfig['fetchFrom'];
					$fetchFromDetails = $sqlParamConfig['fetchFromDetails'];
					$dataType = isset($sqlParamConfig['dataType'])
						? $sqlParamConfig['dataType'] : DatabaseServerDataType::$Default;
					$isRequired = isset($sqlParamConfig['isRequired'])
						? $sqlParamConfig['isRequired'] : false;

					if ($fetchFrom !== 'payload') {
						continue;
					}
					if (
						isset($explainParams[$fetchFromDetails])
						&& $explainParams[$fetchFromDetails]['isRequired'] === true
					) {
						continue;
					}
					$dataType['isRequired'] = $isRequired ? true : false;
					$explainParams[$fetchFromDetails] = $dataType;
				}
			}
		}

		// Check for hierarchy
		$foundHierarchy = false;
		if (isset($sqlConfig['__WHERE__'])) {
			foreach ($sqlConfig['__WHERE__'] as $sqlParamConfig) {
				$fetchFrom = $sqlParamConfig['fetchFrom'];
				$fetchFromDetails = $sqlParamConfig['fetchFromDetails'];
				if (
					in_array(
						needle: $fetchFrom,
						haystack: ['sqlResults', 'sqlParams', 'sqlPayload']
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
					message: 'Invalid config: missing ' . $fetchFrom,
					code: HttpStatus::$InternalServerError
				);
			}
		}

		// Check in subQuery//'__SUB-PAYLOAD__'
		foreach (['__SUB-PAYLOAD__', '__SUB-QUERY__'] as $option) {
			if (isset($sqlConfig[$option])) {
				foreach ($sqlConfig[$option] as $module => &$moduleSqlConfig) {
					$flag = ($flag) ?? $this->getUseHierarchy($moduleSqlConfig);
					$moduleExplainParams = $this->getExplainParams(
						$moduleSqlConfig,
						false,
						$flag
					);
					if ($flag) {
						if (!empty($moduleExplainParams)) {
							$explainParams[$module] = $moduleExplainParams;
						}
					} else {
						foreach ($moduleExplainParams as $fetchFromDetails => $field) {
							if (!isset($explainParams[$fetchFromDetails])) {
								$explainParams[$fetchFromDetails] = $field;
							}
						}
					}
				}
			}
		}

		return $explainParams;
	}

	/**
	 * Function to reset data for module key wise
	 *
	 * @param string $fetchFrom sqlResults / sqlParams / sqlPayload
	 * @param array  $keys      Module Keys in recursion
	 * @param array  $row       Row data fetched from DB
	 *
	 * @return void
	 */
	private function resetFetchData($fetchFrom, $keys, $row): void
	{
		if (
			empty($keys)
			|| count(value: $keys) === 0
		) {
			$this->http->req->s[$fetchFrom] = [];
			$this->http->req->s[$fetchFrom]['return'] = [];
		}
		$httpReq = &$this->http->req->s[$fetchFrom]['return'];
		if (!empty($keys)) {
			foreach ($keys as $k) {
				if (!isset($httpReq[$k])) {
					$httpReq[$k] = [];
				}
				$httpReq = &$httpReq[$k];
			}
		}
		$httpReq = $row;
	}

	/**
	 * Rate Limiting request if configured for Route Sql
	 *
	 * @param array $sqlConfig Config from file
	 *
	 * @return void
	 * @throws \Exception
	 */
	private function rateLimitRoute(&$sqlConfig): void
	{
		if (
			Env::$enableRateLimitAtRouteLevel
			|| !isset($sqlConfig['rateLimitMaxRequest'])
			|| !isset($sqlConfig['rateLimitMaxRequestWindow'])
		) {
			return;
		}

		$payloadSignature = [
			'IP' => $this->http->httpReqDetails['server']['httpRequestIP'],
			'cID' => $this->http->req->cID,
			'httpMethod' => $this->http->httpReqDetails['server']['httpMethod'],
			'Route' => $this->http->httpReqDetails['get'][ROUTE_URL_PARAM],
		];
		if (isset($this->http->req->s['uDetails'])) {
			$payloadSignature['gID'] = ($this->http->req->s['gDetails']['id'] !== null
				? $this->http->req->s['gDetails']['id'] : 0);
			$payloadSignature['uID'] = ($this->http->req->uID !== null
				? $this->http->req->uID : 0);
		}
		$hash = json_encode(value: $payloadSignature);
		$hashKey = md5(string: $hash);

		// @throws \Exception
		$rateLimitChecked = $this->checkRateLimit(
			rateLimitPrefix: Env::$rateLimitRoutePrefix,
			rateLimitMaxRequest: $sqlConfig['rateLimitMaxRequest'],
			rateLimitMaxRequestWindow: $sqlConfig['rateLimitMaxRequestWindow'],
			key: $hashKey
		);
	}

	/**
	 * Check Referrer Lag
	 *
	 * @param array $sqlConfig Config from file
	 *
	 * @return void
	 * @throws \Exception
	 */
	private function checkReferrerLag(&$sqlConfig): void
	{

		$customerUserReferrerLagKey = CacheServerKey::customerUserReferrerLag(
			cID: $this->http->req->cID
			uID: $this->http->req->uID
		);
		if (
			isset($sqlConfig['referrerLagWindow'])
			&& count($sqlConfig['referrerLagWindow']) > 0
		) {
			if (!DbCommonFunction::$gCacheServer->cacheExists(key: $customerUserReferrerLagKey)) {
				throw new \Exception(
					message: 'Referrer lag not initiated',
					code: HttpStatus::$BadRequest
				);
			}
			$referrerLagDetails = json_decode(
				json: DbCommonFunction::$gCacheServer->getCache(
					key: $customerUserReferrerLagKey
				),
				associative: true
			);
			if (
				isset($referrerLagDetails['initRoute'])
				&& isset($referrerLagDetails['timestamp'])
			) {
				$found = false;
				foreach ($sqlConfig['referrerLagWindow'] as $referrerSqlConfig) {
					if ($referrerLagDetails['initRoute'] === $referrerSqlConfig['referrer']) {
						$tsDiff = Env::$timestamp - $referrerSqlConfig['timestamp'];
						if (
							isset($referrerSqlConfig['minimumReferrerLagWindow'])
							&& $tsDiff >= $referrerSqlConfig['minimumReferrerLagWindow']
						) {
							if (isset($referrerSqlConfig['maximumReferrerLagWindow'])) {
								if ($tsDiff <= $referrerSqlConfig['maximumReferrerLagWindow']) {
									$found = true;
								} else {
									DbCommonFunction::$gCacheServer->deleteCache(key: $customerUserReferrerLagKey);
								}
							} else {
								$found = true;
							}
						} else {
							DbCommonFunction::$gCacheServer->deleteCache(key: $customerUserReferrerLagKey);
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
			&& $sqlConfig['enableReferrerLag'] === true
		) {
			if (!DbCommonFunction::$gCacheServer->cacheExists(key: $customerUserReferrerLagKey)) {
				DbCommonFunction::$gCacheServer->setCache(
					key: $customerUserReferrerLagKey,
					value: json_encode(value: [
						'initRoute' => $this->http->req->rParser->configuredRoute,
						'timestamp' => Env::$timestamp
					])
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
	 * @param array $sqlConfig       Config from file
	 * @param array $payloadIndexes Payload Indexes
	 *
	 * @return array
	 */
	private function checkIdempotent(&$sqlConfig, $payloadIndexes): array
	{
		$idempotentWindow = 0;
		$hashKey = null;
		$hashJson = null;
		if (
			isset($sqlConfig['idempotentWindow'])
			&& is_numeric(value: $sqlConfig['idempotentWindow'])
			&& $sqlConfig['idempotentWindow'] > 0
		) {
			$idempotentWindow = (int)$sqlConfig['idempotentWindow'];
			if ($idempotentWindow) {
				$payloadSignature = [
					'idempotentSecret' => Env::$idempotentSecret,
					'idempotentWindow' => $idempotentWindow,
					'IP' => $this->http->httpReqDetails['server']['httpRequestIP'],
					'cID' => $this->http->req->cID,
					'httpMethod' => $this->http->httpReqDetails['server']['httpMethod'],
					'Route' => $this->http->httpReqDetails['get'][ROUTE_URL_PARAM],
					'payload' => $this->http->req->dataDecode->get(
						implode(separator: ':', array: $payloadIndexes)
					)
				];
				if (isset($this->http->req->s['uDetails'])) {
					$payloadSignature['gID'] = ($this->http->req->s['gDetails']['id'] !== null
						? $this->http->req->s['gDetails']['id'] : 0);
					$payloadSignature['uID'] = ($this->http->req->uID !== null
						? $this->http->req->uID : 0);
				}

				$hash = json_encode(value: $payloadSignature);
				$hashKey = md5(string: $hash);
				if (DbCommonFunction::$gCacheServer->cacheExists(key: $hashKey)) {
					$hashJson = str_replace(
						search: 'JSON',
						replace: DbCommonFunction::$gCacheServer->getCache(key: $hashKey),
						subject: '{"Idempotent": JSON, "Status": 200}'
					);
				}
			}
		}

		return [$idempotentWindow, $hashKey, $hashJson];
	}

	/**
	 * Lag Response
	 *
	 * @param array $sqlConfig Config from file
	 *
	 * @return void
	 */
	private function lagResponse($sqlConfig): void
	{
		if (
			isset($sqlConfig['responseLag'])
			&& isset($sqlConfig['responseLag'])
		) {
			$payloadSignature = [
				'IP' => $this->http->httpReqDetails['server']['httpRequestIP'],
				'cID' => $this->http->req->cID,
				'httpMethod' => $this->http->httpReqDetails['server']['httpMethod'],
				'Route' => $this->http->httpReqDetails['get'][ROUTE_URL_PARAM],
			];
			if (isset($this->http->req->s['uDetails'])) {
				$payloadSignature['gID'] = ($this->http->req->s['gDetails']['id'] !== null
					? $this->http->req->s['gDetails']['id'] : 0);
				$payloadSignature['uID'] = ($this->http->req->uID !== null
					? $this->http->req->uID : 0);
			}

			$hash = json_encode(value: $payloadSignature);
			$hashKey = 'LAG:' . md5(string: $hash);

			if (DbCommonFunction::$gCacheServer->cacheExists(key: $hashKey)) {
				$noOfRequest = DbCommonFunction::$gCacheServer->getCache(key: $hashKey);
			} else {
				$noOfRequest = 0;
			}

			DbCommonFunction::$gCacheServer->setCache(
				key: $hashKey,
				value: ++$noOfRequest,
				expire: 3600
			);

			$lag = 0;
			$responseLag = &$sqlConfig['responseLag'];
			if (is_array(value: $responseLag)) {
				foreach ($responseLag as $start => $newLag) {
					if ($noOfRequest > $start) {
						$lag = $newLag;
					}
				}
			}

			if ($lag > 0) {
				sleep(seconds: $lag);
			}
		}
	}

	/**
	 * Check Rate Limit
	 *
	 * @param string $rateLimitPrefix        Prefix
	 * @param int    $rateLimitMaxRequest   Max request
	 * @param int    $rateLimitMaxRequestWindow Window in seconds
	 * @param string $key                    Key
	 *
	 * @return void
	 * @throws \Exception
	 */
	public function checkRateLimit(
		$rateLimitPrefix,
		$rateLimitMaxRequest,
		$rateLimitMaxRequestWindow,
		$key
	): bool {
		if ($this->rateLimiter === null) {
			$this->rateLimiter = new RateLimiter($this->http->req);
		}

		try {
			$result = $this->rateLimiter->check(
				prefix: $rateLimitPrefix,
				maxRequest: $rateLimitMaxRequest,
				secondsWindow: $rateLimitMaxRequestWindow,
				key: $key
			);

			if ($result['allowed']) {
				// Process the request
				return true;
			} else {
				// Return 429 Too Many Request
				throw new \Exception(
					message: $result['resetAt'] - Env::$timestamp,
					code: HttpStatus::$TooManyRequest
				);
			}
		} catch (\Exception $e) {
			// Handle connection errors
			throw new \Exception(
				message: $e->getMessage(),
				code: $e->getCode()
			);
		}
	}

	/**
	 * Get Trigger Data
	 *
	 * @param array $triggerConfig Trigger Config
	 *
	 * @return mixed
	 */
	public function getTriggerData($triggerConfig): mixed
	{
		if (!isset($this->http->req->s['token'])) {
			throw new \Exception(
				message: 'Missing token',
				code: HttpStatus::$InternalServerError
			);
		}

		$httpReqDetails = [];

		$isAssoc = (!isset($triggerConfig[0])) ? true : false;
		if (
			!$isAssoc
			&& isset($triggerConfig[0])
			&& count(value: $triggerConfig) === 1
		) {
			$triggerConfig = $triggerConfig[0];
			$isAssoc = true;
		}

		$triggerOutput = [];
		if ($isAssoc) {
			$httpReqDetails = $this->getTriggerHttp($triggerConfig);
			[$responseheaders, $responseContent, $responseCode] = Start::http(httpReqDetails: $httpReqDetails);
			$triggerOutput = &$responseContent;
		} else {
			for (
				$iTrigger = 0, $iTriggerCount = count($triggerConfig);
				$iTrigger < $iTriggerCount;
				$iTrigger++
			) {
				$httpReqDetails = $this->getTriggerHttp($triggerConfig[$iTrigger]);
				[$responseheaders, $responseContent, $responseCode] = Start::http(httpReqDetails: $httpReqDetails);
				$triggerOutput[] = &$responseContent;
			}
		}

		return $triggerOutput;
	}

	/**
	 * Get Trigger Details
	 *
	 * @param array $triggerConfig Trigger Config
	 *
	 * @return mixed
	 */
	public function getTriggerHttp($triggerConfig)
	{
		$method = $triggerConfig['__METHOD__'];
		[$routeElementsArr, $errors] = $this->getTriggerParams(
			payloadConfig: $triggerConfig['__ROUTE__']
		);

		if ($errors) {
			return $errors;
		}

		$route = '/' . implode(separator: '/', array: $routeElementsArr);

		$queryStringArr = [];
		$payloadArr = [];

		if (isset($triggerConfig['__QUERY-STRING__'])) {
			[$queryStringArr, $errors] = $this->getTriggerParams(
				payloadConfig: $triggerConfig['__QUERY-STRING__']
			);

			if ($errors) {
				return $errors;
			}
		}
		if (isset($triggerConfig['__PAYLOAD__'])) {
			[$payloadArr, $errors] = $this->getTriggerParams(
				payloadConfig: $triggerConfig['__PAYLOAD__']
			);
			if ($errors) {
				return $errors;
			}
		}

		$httpReqDetails['streamData'] = false;
		$httpReqDetails['server']['domainName'] = $this->http->httpReqDetails['server']['domainName'];
		$httpReqDetails['server']['httpMethod'] = $method;
		$httpReqDetails['server']['httpRequestIP'] = $this->http->httpReqDetails['server']['httpRequestIP'];
		$httpReqDetails['header'] = $this->http->httpReqDetails['header'];
		$httpReqDetails['post'] = json_encode($payloadArr);
		$httpReqDetails['get'] = $queryStringArr;
		$httpReqDetails['get'][ROUTE_URL_PARAM] = $route;
		$httpReqDetails['isWebRequest'] = false;

		return $httpReqDetails;
	}

	/**
	 * Generates Params for statement to execute
	 *
	 * @param array $payloadConfig    API Payload configuration
	 * @param array $payloadVariables Payload Variables
	 *
	 * @return array
	 * @throws \Exception
	 */
	private function getTriggerParams(&$payloadConfig): array
	{
		$sqlParams = [];
		$errors = [];

		// Collect param values as per config respectively
		foreach ($payloadConfig as &$payloadParamConfig) {
			$var = $payloadParamConfig['column'] ?? null;

			$fetchFrom = $payloadParamConfig['fetchFrom'];
			$fetchFromDetails = $payloadParamConfig['fetchFromDetails'];
			if ($fetchFrom === 'function') {
				$function = $fetchFromDetails;
				$value = $function($this->http->req->s);
				if ($var === null) {
					$sqlParams[] = $value;
				} else {
					$sqlParams[$var] = $value;
				}
				continue;
			} elseif (
				in_array(
					needle: $fetchFrom,
					haystack: ['sqlResults', 'sqlParams', 'sqlPayload']
				)
			) {
				$fetchFromKeys = explode(separator: ':', string: $fetchFromDetails);
				$value = $this->http->req->s[$fetchFrom];
				foreach ($fetchFromKeys as $key) {
					if (!isset($value[$key])) {
						throw new \Exception(
							message: 'Invalid hierarchy:  Missing hierarchy data',
							code: HttpStatus::$InternalServerError
						);
					}
					$value = $value[$key];
				}
				if ($var === null) {
					$sqlParams[] = $value;
				} else {
					$sqlParams[$var] = $value;
				}
				continue;
			} elseif ($fetchFrom === 'custom') {
				$value = $fetchFromDetails;
				if ($var === null) {
					$sqlParams[] = $value;
				} else {
					$sqlParams[$var] = $value;
				}
				continue;
			} elseif (isset($this->http->req->s[$fetchFrom][$fetchFromDetails])) {
				$value = $this->http->req->s[$fetchFrom][$fetchFromDetails];
				if ($var === null) {
					$sqlParams[] = $value;
				} else {
					$sqlParams[$var] = $value;
				}
				continue;
			} else {
				$errors[] = "Invalid configuration of '{$fetchFrom}' for '{$fetchFromDetails}'";
				continue;
			}
		}

		return [$sqlParams, $errors];
	}

	/**
	 * Process import function of configuration
	 *
	 * @param array $wSqlConfig   Config from file
	 * @param bool  $useHierarchy Use results in where clause of sub queries
	 *
	 * @return string
	 */
	private function processImportConfig(&$wSqlConfig, $useHierarchy): string
	{
		$explainParams = $this->getExplainParams(
			sqlConfig: $wSqlConfig,
			isFirstCall: true,
			flag: $useHierarchy
		);
		$params = $this->genCsvHelper(
			header: 'CSV',
			explainParams: $explainParams
		);

		$header = [];
		$header[] = '__mode__';
		foreach ($params as $r => $p) {
			if (is_array($p)) {
				for ($i = 0, $iCount = count($p); $i < $iCount; $i++) {
					$header[] = $p[$i];
				}
			} else {
				$header[] = $p;
			}
		}
		$csv = '"' . implode(separator: '","', array: $header) . '"' . PHP_EOL;
		$blankStr = '';
		foreach ($params as $r => $p) {
			if ($r === 'CSV') {
				for ($i = 1, $iCount = count($header); $i < $iCount; $i++) {
					$blankStr = ',""';
				}
			}
			$csv .= "{$r}{$blankStr}" . PHP_EOL;
		}

		return $csv;
	}

	/**
	 * Generate sample CSV helper
	 *
	 * @param string $header
	 * @param array  $explainParams
	 *
	 * @return array
	 */
	private function genCsvHelper($header, $explainParams): array
	{
		$fields = [];
		foreach ($explainParams as $key => $value) {
			if (isset($value['dataType'])) {
				$fields[$header][] = "{$header}:{$key}";
			} else {
				$returnHeader = $this->genCsvHelper("{$header}:{$key}", $value);
				foreach ($returnHeader as $k => $v) {
					$fields[$k] = $v;
				}
			}
		}

		return $fields;
	}
}
