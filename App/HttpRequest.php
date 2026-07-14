<?php

/**
 * HTTP request
 * php version 8.3
 *
 * @category  HTTP request
 * @package   Microservices
 * @author    Ramesh N. Jangid (Sharma) <polygon.co.in@gmail.com>
 * @copyright © 2026 Ramesh N. Jangid (Sharma)
 * @license   MIT https://opensource.org/license/mit
 * @link      https://github.com/polygoncoin/Microservices
 * @since     Class available since Release 1.0.0
 */

namespace Microservices\App;

use Microservices\App\Auth;
use Microservices\App\CacheServerKey;
use Microservices\App\CommonFunction;
use Microservices\App\Constant;
use Microservices\App\DataRepresentation\DataDecode;
use Microservices\App\DataRepresentation\DataEncode;
use Microservices\App\DbCommonFunction;
use Microservices\App\Env;
use Microservices\App\Http;
use Microservices\App\HttpStatus;
use Microservices\App\QueryCache;
use Microservices\App\RateLimiter;
use Microservices\App\RouteParser;
use Microservices\App\Server\CacheServer;
use Microservices\App\Server\DatabaseServer;
use Microservices\App\SessionHandler\Session;

/**
 * HTTP request
 * php version 8.3
 *
 * @category  HTTP request
 * @package   Microservices
 * @author    Ramesh N. Jangid (Sharma) <polygon.co.in@gmail.com>
 * @copyright © 2026 Ramesh N. Jangid (Sharma)
 * @license   MIT https://opensource.org/license/mit
 * @link      https://github.com/polygoncoin/Microservices
 * @since     Class available since Release 1.0.0
 */
class HttpRequest
{
	/**
	 * Input Representation
	 *
	 * @var null|string
	 */
	public $inputRepresentation = null;

	/**
	 * Routes Configuration Directory
	 *
	 * @var null|string
	 */
	public $ROUTES_DIR = null;

	/**
	 * SQL & Payload Configuration Directory
	 * Payload Configuration Directory for Supplement
	 *
	 * @var null|string
	 */
	public $QUERIES_DIR = null;

	/**
	 * Rate Limiter
	 *
	 * @var null|RateLimiter
	 */
	public $rateLimiterObj = null;

	/**
	 * Auth middleware object
	 *
	 * @var null|Auth
	 */
	public $authObj = null;

	/**
	 * Request id
	 *
	 * @var null|int
	 */
	public $requestId = null;

	/**
	 * Data Decode object
	 *
	 * @var null|DataDecode
	 */
	public $dataDecodeObj = null;

	/**
	 * HTTP object
	 *
	 * @var null|Http
	 */
	private $httpObj = null;

	/**
	 * Customer Cache Object
	 *
	 * @var null|CacheServer
	 */
	public $customerCacheObj = null;

	/**
	 * Customer Query Cache Object
	 *
	 * @var null|CacheServer
	 */
	public $customerQueryCacheObj = null;

	/**
	 * Customer Database Object
	 *
	 * @var null|DatabaseServer
	 */
	public $customerDbObj = null;

	/**
	 * Session detail of a request
	 *
	 * @var null|array
	 */
	public $session = null;

	/**
	 * Public domain cache key exist flag
	 *
	 * @var null|bool
	 */
	public $isPublicDomain = null;

	/**
	 * Private session domain cache key exist flag
	 *
	 * @var null|bool
	 */
	public $isPrivateSessionDomain = null;

	/**
	 * Private token domain cache key exist flag
	 *
	 * @var null|bool
	 */
	public $isPrivateTokenDomain = null;

	/**
	 * Domain cache key
	 *
	 * @var null|bool
	 */
	public $domainCacheKey = null;

	/**
	 * Flag for Private request
	 *
	 * @var null|bool
	 */
	public $isPrivateRequest = null;

	/**
	 * Flag for Public request
	 *
	 * @var null|bool
	 */
	public $isPublicRequest = null;

	/**
	 * Payload stream
	 */
	public $payloadStream = null;

	/**
	 * Route Parser object
	 *
	 * @var null|RouteParser
	 */
	public $routeParserObj = null;

	/**
	 * Customer Id
	 *
	 * @var null|int
	 */
	public $customerId = null;

	/**
	 * Group Id
	 *
	 * @var null|int
	 */
	public $customerUserGroupId = null;

	/**
	 * User Id
	 *
	 * @var null|int
	 */
	public $customerUserId = null;

	/**
	 * Session object
	 *
	 * @var null|Session
	 */
	public $sessionObj = null;

	/**
	 * Constructor
	 *
	 * @param Http $httpObj
	 */
	public function __construct(
		Http &$httpObj
	) {
		$this->httpObj = &$httpObj;
		$this->inputRepresentation = Env::$inputRepresentation;

		DbCommonFunction::connectGlobalCache();

		$this->isPublicDomain = false;
		$this->isPrivateSessionDomain = false;
		$this->isPrivateTokenDomain = false;

		$publicDomainCacheKey = CacheServerKey::publicDomain(
			domainName: $this->httpObj->httpReqData['server']['domainName']
		);
		if (
			DbCommonFunction::$globalCacheServerObj->cacheExist(
				cacheKey: $publicDomainCacheKey
			)
		) {
			$this->isPublicDomain = true;
			$this->domainCacheKey = $publicDomainCacheKey;
			$this->isPrivateRequest = false;
			$this->isPublicRequest = true;
		}
		if (!$this->isPublicDomain) {
			$privateSessionDomainCacheKey = CacheServerKey::privateSessionDomain(
				domainName: $this->httpObj->httpReqData['server']['domainName']
			);
			if (
				DbCommonFunction::$globalCacheServerObj->cacheExist(
					cacheKey: $privateSessionDomainCacheKey
				)
			) {
				$this->isPrivateSessionDomain = true;
				$this->domainCacheKey = $privateSessionDomainCacheKey;
				$this->isPrivateRequest = true;
				$this->isPublicRequest = false;
			}
		}
		if (
			!$this->isPublicDomain
			&& !$this->isPrivateSessionDomain
		) {
			$privateTokenDomainCacheKey = CacheServerKey::privateTokenDomain(
				domainName: $this->httpObj->httpReqData['server']['domainName']
			);
			if (
				DbCommonFunction::$globalCacheServerObj->cacheExist(
					cacheKey: $privateTokenDomainCacheKey
				)
			) {
				$this->isPrivateTokenDomain = true;
				$this->domainCacheKey = $privateTokenDomainCacheKey;
				$this->isPrivateRequest = true;
				$this->isPublicRequest = false;
			}
		}
	}

	/**
	 * Initialize
	 *
	 * @return bool
	 */
	public function init(): bool
	{
		if (
			!$this->isPublicDomain
			&& !$this->isPrivateSessionDomain
			&& !$this->isPrivateTokenDomain
			&& $this->httpObj->httpReqData['get'][ROUTE_URL_PARAM] !== '/' . Env::$reloadRequestRoutePrefix
		) {
			throw new \Exception(
				message: "Invalid domain: '{$this->httpObj->httpReqData['server']['domainName']}'",
				code: HttpStatus::$BadRequest
			);
		}

		$this->session['customerData'] = DbCommonFunction::$globalCacheServerObj->cacheGet(
			cacheKey: $this->domainCacheKey
		);
		$this->customerId = $this->session['customerData']['customer_id'];

		if ($this->isPrivateSessionDomain) {
			$this->sessionObj = new Session();
			$this->sessionObj->sessionDomain = $this->httpObj->httpReqData['server']['domainName'];
			$this->sessionObj->initSessionHandler(
				customerData: $this->session['customerData'],
				options: []
			);
			$this->sessionObj->sessionStartReadonly();
		}

		if (
			$this->isPublicRequest
			&& !CommonFunction::isEnabled(
				httpObj: $this->httpObj,
				feature: 'customer_enabled_public_request'
			)
		) {
			throw new \Exception(
				message: 'Public request are disabled',
				code: HttpStatus::$BadRequest
			);
		}

		if (
			$this->isPrivateRequest
			&& !CommonFunction::isEnabled(
				httpObj: $this->httpObj,
				feature: 'customer_enabled_private_request'
			)
		) {
			throw new \Exception(
				message: 'Private request are disabled',
				code: HttpStatus::$BadRequest
			);
		}

		if (
			(
				$this->isPublicRequest
				&& CommonFunction::isEnabled(
					httpObj: $this->httpObj,
					feature: 'customer_enabled_query_cache_for_public_request'
				)
			)
			|| (
				$this->isPrivateRequest
				&& CommonFunction::isEnabled(
					httpObj: $this->httpObj,
					feature: 'customer_enabled_query_cache_for_private_request'
				)
			)
		) {
			$this->customerQueryCacheObj = new QueryCache(
				$this->httpObj
			);
		}

		if ($this->isPrivateRequest) {
			$this->customerCacheObj = DbCommonFunction::connectCustomerCache(
				customerData: $this->session['customerData']
			);
			if (
				CommonFunction::isEnabled(
					httpObj: $this->httpObj,
					feature: 'customer_enabled_rate_limiting'
				)
			) {
				$this->rateLimiterObj = new RateLimiter(
					cacheObj: $this->customerCacheObj
				);
			}
		}

		if ($this->httpObj->httpReqData['get'][ROUTE_URL_PARAM] !== '/login') {
			if ($this->isPrivateRequest) {
				$this->authObj = new Auth(
					httpObj: $this->httpObj
				);
				$this->authObj->loadUserData();
				$this->authObj->loadGroupData();
			}

			$this->routeParserObj = new RouteParser(
				httpObj: $this->httpObj
			);
			$this->routeParserObj->parseRoute();

			if ($this->httpObj->responseObj !== null) {
				$this->httpObj->initResponse();
			}
		}

		return true;
	}

	/**
	 * Load payload
	 *
	 * @return void
	 */
	public function loadPayload(): void
	{
		if (isset($this->session['payloadType'])) {
			return;
		}

		$payloadJson = "{}";

		$this->urlDecode(
			values: $this->httpObj->httpReqData['get']
		);
		$this->session['queryParamArr'] = &$this->httpObj->httpReqData['get'];

		switch ($this->httpObj->httpReqData['server']['httpMethod']) {
			case Constant::$QUERY:
			case Constant::$POST:
			case Constant::$PUT:
			case Constant::$PATCH:
			case Constant::$DELETE:
				$payloadJson = $this->setPayloadStream();
				rewind(
					stream: $this->payloadStream
				);

				$this->dataDecodeObj = new DataDecode(
					inputRepresentation: $this->inputRepresentation,
					dataFileHandle: $this->payloadStream
				);

				$this->dataDecodeObj->init();
				$this->dataDecodeObj->indexData();

				$this->session['payloadType'] = $this->dataDecodeObj->dataType();
				break;
		}

		$this->requestId = $this->getRequestId(
			customerId: $this->customerId,
			customerUserGroupId: $this->customerUserGroupId,
			customerUserId: $this->customerUserId,
			route: $this->httpObj->httpReqData['get'][ROUTE_URL_PARAM],
			httpMethod: $this->httpObj->httpReqData['server']['httpMethod'],
			httpRequestIp: $this->httpObj->httpReqData['server']['httpRequestIp'],
			payloadJson: $payloadJson
		);
	}

	/**
	 * Set payload stream
	 *
	 * @return string
	 */
	private function setPayloadStream(): string
	{
		$payloadJson = '{}';
		switch (true) {
			case (
				$this->httpObj->httpReqData['get'][ROUTE_URL_PARAM] !== '/login'
				&& $this->routeParserObj->routeEndingWithReservedKeywordFlag
				&& ($this->routeParserObj->routeEndingReservedKeyword === Env::$importRequestRouteKeyword)
				&& isset($this->httpObj->httpReqData['files']['file']['tmp_name'])
			):
				$uploadedFileName = $this->httpObj->httpReqData['files']['file']['tmp_name'];
				$uploadedFileMd5 = md5_file(
					$this->httpObj->httpReqData['files']['file']['tmp_name']
				);

				$this->customerDbObj = DbCommonFunction::connectCustomerDb(
					customerData: $this->httpObj->requestObj->session['customerData'],
					fetchFrom: 'Master'
				);
				$uploadedFileMd5Data = $this->getUploadedFileMd5Data(uploadedFileMd5: $uploadedFileMd5);

				if ($uploadedFileMd5Data !== false) {
					throw new \Exception(
						message: "Same file was already uploaded on '{$uploadedFileMd5Data['uploaded_on']}'",
						code: HttpStatus::$BadRequest
					);
				}

				$sql = 'INSERT INTO `import_file_detail` SET
					customer_id = :customer_id,
					customer_user_group_id = :customer_user_group_id,
					customer_user_id = :customer_user_id,
					uploaded_file_name = :uploaded_file_name,
					uploaded_file_md5 = :uploaded_file_md5,
					request_ip = :request_ip
				';
				$paramArr[':customer_id'] = $this->customerId;
				$paramArr[':customer_user_group_id'] = $this->customerUserGroupId;
				$paramArr[':customer_user_id'] = $this->customerUserId;
				$paramArr[':uploaded_file_name'] = $uploadedFileName;
				$paramArr[':uploaded_file_md5'] = $uploadedFileMd5;
				$paramArr[':request_ip'] = $this->httpObj->httpReqData['server']['httpRequestIp'];

				$this->customerDbObj->execQuery(
					sql: $sql,
					paramArr: $paramArr
				);
				$importFileMd5Id = $this->customerDbObj->lastInsertId();

				$payloadJson = $this->formatCsvPayload(
					csvFile: $this->httpObj->httpReqData['files']['file']['tmp_name']
				);
				break;
			case $this->inputRepresentation === 'XML':
				$payloadJson = $this->convertXmlToJson(
					xmlString: $this->httpObj->httpReqData['post']
				);
				break;
			default:
				$payloadJson = $this->httpObj->httpReqData['post'];
		}

		$this->payloadStream = fopen(
			filename: "php://memory",
			mode: "rw+b"
		);
		fwrite(
			stream: $this->payloadStream,
			data: $payloadJson
		);

		return $payloadJson;
	}

	/**
	 * Get Request Id
	 *
	 * @param string $uploadedFileMd5
	 *
	 * @return mixed
	 */
	public function getUploadedFileMd5Data(
		$uploadedFileMd5
	): mixed {
		$uploadedFileMd5Data = false;

		$sql = "SELECT
				*
			FROM
				`import_file_detail`
			WHERE
				`uploaded_file_md5` = :uploaded_file_md5
				AND `is_disabled` = 'No'
				AND `is_deleted` = 'No'
		";
		$paramArr[':uploaded_file_md5'] = $uploadedFileMd5;

		$this->customerDbObj->execQuery(
			sql: $sql,
			paramArr: $paramArr
		);
		if ($record = $this->customerDbObj->fetch()) {
			$uploadedFileMd5Data = &$record;
		}

		return $uploadedFileMd5Data;
	}

	/**
	 * Get Request Id
	 *
	 * @param int    $customerId
	 * @param int    $customerUserGroupId
	 * @param int    $customerUserId
	 * @param string $route
	 * @param string $httpMethod
	 * @param string $httpRequestIp
	 * @param string $payloadJson
	 *
	 * @return int
	 */
	public function getRequestId(
		&$customerId,
		&$customerUserGroupId,
		&$customerUserId,
		&$route,
		&$httpMethod,
		&$httpRequestIp,
		&$payloadJson
	): int {
		$requestId = 0;
		if ($this->isPrivateRequest) {
			DbCommonFunction::connectGlobalDb();
			$sql = 'INSERT INTO `request` SET
				customer_id = :customer_id,
				customer_user_group_id = :customer_user_group_id,
				customer_user_id = :customer_user_id,
				request_route = :request_route,
				request_method = :request_method,
				request_ip = :request_ip,
				request_payload_json = :request_payload_json
			';
			$paramArr[':customer_id'] = $customerId;
			$paramArr[':customer_user_group_id'] = $customerUserGroupId;
			$paramArr[':customer_user_id'] = $customerUserId;
			$paramArr[':request_route'] = $route;
			$paramArr[':request_method'] = $httpMethod;
			$paramArr[':request_ip'] = $httpRequestIp;
			$paramArr[':request_payload_json'] = $payloadJson;

			DbCommonFunction::$gDbServer->execQuery(
				sql: $sql,
				paramArr: $paramArr
			);
			$requestId = DbCommonFunction::$gDbServer->lastInsertId();
		}

		return $requestId;
	}

	/**
	 * Log Debug Data
	 *
	 * @param string $debugMode
	 * @param string $debugJson
	 *
	 * @return int
	 */
	public function logDebugData(
		$debugMode,
		$debugJson
	): int {
		$logId = 0;
		if ($this->isPrivateRequest) {
			DbCommonFunction::connectGlobalDb();
			$sql = 'INSERT INTO `debug_log` SET
				debug_mode = :debug_mode,
				request_id = :request_id,
				customer_id = :customer_id,
				customer_user_group_id = :customer_user_group_id,
				customer_user_id = :customer_user_id,
				request_route = :request_route,
				request_method = :request_method,
				request_payload_json = :request_payload_json,
				request_config_json = :request_config_json,
				request_session_json = :request_session_json,
				request_exception_json = :request_exception_json,
				request_ip = :request_ip
			';
			$paramArr[':debug_mode'] = $debugMode;
			$paramArr[':request_id'] = $this->requestId;
			$paramArr[':customer_id'] = $this->customerId;
			$paramArr[':customer_user_group_id'] = $this->customerUserGroupId;
			$paramArr[':customer_user_id'] = $this->customerUserId;
			$paramArr[':request_route'] = $this->httpObj->httpReqData['get'][ROUTE_URL_PARAM];
			$paramArr[':request_method'] = $this->httpObj->httpReqData['server']['httpMethod'];
			$paramArr[':request_payload_json'] = isset($this->session['payload']) ? json_encode(
				value: $this->session['payload']
			) : '{}';
			$paramArr[':request_config_json'] = isset($this->routeParserObj->sqlConfig) ? json_encode(
				value: $this->routeParserObj->sqlConfig
			) : '{}';
			$paramArr[':request_session_json'] = isset($this->session) ? json_encode(
				value: $this->session
			) : '{}';
			$paramArr[':request_debug_json'] = $debugJson;
			$paramArr[':request_ip'] = $this->httpObj->httpReqData['server']['httpRequestIp'];

			DbCommonFunction::$gDbServer->execQuery(
				sql: $sql,
				paramArr: $paramArr
			);
			$logId = DbCommonFunction::$gDbServer->lastInsertId();
		}

		return $logId;
	}

	/**
	 * Log Error Data
	 *
	 * @param string $exceptionJson
	 *
	 * @return int
	 */
	public function logErrorData(
		$exceptionJson
	): int {
		$logId = 0;
		if ($this->isPrivateRequest) {
			DbCommonFunction::connectGlobalDb();
			$sql = 'INSERT INTO `error_log` SET
				request_id = :request_id,
				customer_id = :customer_id,
				customer_user_group_id = :customer_user_group_id,
				customer_user_id = :customer_user_id,
				request_route = :request_route,
				request_method = :request_method,
				request_payload_json = :request_payload_json,
				request_config_json = :request_config_json,
				request_session_json = :request_session_json,
				request_exception_json = :request_exception_json,
				request_ip = :request_ip
			';
			$paramArr[':request_id'] = $this->requestId;
			$paramArr[':customer_id'] = $this->customerId;
			$paramArr[':customer_user_group_id'] = $this->customerUserGroupId;
			$paramArr[':customer_user_id'] = $this->customerUserId;
			$paramArr[':request_route'] = $this->httpObj->httpReqData['get'][ROUTE_URL_PARAM];
			$paramArr[':request_method'] = $this->httpObj->httpReqData['server']['httpMethod'];
			$paramArr[':request_payload_json'] = isset($this->session['payload']) ? json_encode(
				value: $this->session['payload']
			) : '{}';
			$paramArr[':request_config_json'] = isset($this->routeParserObj->sqlConfig) ? json_encode(
				value: $this->routeParserObj->sqlConfig
			) : '{}';
			$paramArr[':request_session_json'] = isset($this->session) ? json_encode(
				value: $this->session
			) : '{}';
			$paramArr[':request_exception_json'] = $exceptionJson;
			$paramArr[':request_ip'] = $this->httpObj->httpReqData['server']['httpRequestIp'];

			DbCommonFunction::$gDbServer->execQuery(
				sql: $sql,
				paramArr: $paramArr
			);
			$logId = DbCommonFunction::$gDbServer->lastInsertId();
		}

		return $logId;
	}

	/**
	 * Convert XML to JSON
	 *
	 * @param string $xmlString
	 *
	 * @return string
	 */
	private function convertXmlToJson(
		$xmlString
	): string {
		$xml = simplexml_load_string(
			data: $xmlString
		);
		$arrayFromXml = CommonFunction::jsonDecode(
			value: json_encode(
				value: $xml
			)
		);
		unset($xml);

		$result = [];
		$this->formatXmlArray(
			arrayFromXml: $arrayFromXml,
			result: $result
		);

		return json_encode(
			value: $result
		);
	}

	/**
	 * Format Array generated by XML
	 *
	 * @param array $arrayFromXml Array generated by XML
	 * @param array $result       Formatted array
	 *
	 * @return void
	 */
	private function formatXmlArray(
		&$arrayFromXml,
		&$result
	): void {
		if (
			isset($arrayFromXml['Records'])
			&& is_array(
				value: $arrayFromXml['Records']
			)
		) {
			$arrayFromXml = &$arrayFromXml['Records'];
		}

		if (
			isset($arrayFromXml['Record'])
			&& is_array(
				value: $arrayFromXml['Record']
			)
		) {
			$arrayFromXml = &$arrayFromXml['Record'];
		}

		if (
			isset($arrayFromXml[0])
			&& is_array(
				value: $arrayFromXml[0]
			)
			&& count(
				value: $arrayFromXml
			) === 1
		) {
			$arrayFromXml = &$arrayFromXml[0];
			if (empty($arrayFromXml)) {
				return;
			}
		}

		if (
			!is_array(
				value: $arrayFromXml
			)
		) {
			return;
		}

		$xmlAttributeColumn = 'attribute';
		foreach ($arrayFromXml as $column => &$columnValue) {
			if ($column === $xmlAttributeColumn) {
				foreach ($columnValue as $attributeKey => &$attributeKeyValue) {
					$result[$attributeKey] = $attributeKeyValue;
				}
				continue;
			}
			if (
				is_array(
					value: $columnValue
				)
			) {
				$result[$column] = [];
				$this->formatXmlArray(
					arrayFromXml: $columnValue,
					result: $result[$column]
				);
				continue;
			}
			$result[$column] = $columnValue;
		}
	}

	/**
	 * urldecode string or array
	 *
	 * @param array|string $value Array vales to be decoded. Basically $httpReqData['get']
	 *
	 * @return void
	 */
	public function urlDecode(
		&$values
	): void {
		if (
			is_array(
				value: $values
			)
		) {
			foreach ($values as &$value) {
				if (
					is_array(
						value: $value
					)
				) {
					$this->urlDecode(
						values: $value
					);
				} else {
					$value = urldecode(
						string: $value
					);
				}
			}
		} else {
			$values = urldecode(
				string: $values
			);
		}
	}

	/**
	 * Format CSV Payload
	 *
	 * @param string $csvFile
	 *
	 * @return string
	 */
	public function formatCsvPayload(
		$csvFile
	): string {
		$dataEncodeObj = new DataEncode(
			httpObj: $this->httpObj
		);
		$dataEncodeObj->init(
			header: false
		);
		$dataEncodeObj->startObject();

		$csvHeaderData = false;
		$counter = null;
		$currentModeArr = [];

		$fp = fopen($csvFile, "r");
		while (($csvString = fgets($fp)) !== false) {
			if (empty($csvString)) {
				continue;
			}
			$csvRecordArr = str_getcsv(
				$csvString,
				",",
				"\"",
				"\\"
			);
			if (empty($csvRecordArr)) {
				continue;
			}
			if ($csvHeaderData === false) {
				$csvHeaderData = [];
				foreach ($csvRecordArr as $columnPosition => $value) {
					$values = explode(
						':',
						$value
					);
					$_csvHeaderData = &$csvHeaderData;
					$indexCount = count(
						value: $values
					);
					for ($index = 0; $index < $indexCount; $index++) {
						if (($index+1) === $indexCount) {
							$_csvHeaderData['__column__'][$values[$index]] = $columnPosition;
						} else {
							if (!isset($_csvHeaderData[$values[$index]])) {
								$_csvHeaderData[$values[$index]] = [];
							}
							$_csvHeaderData = &$_csvHeaderData[$values[$index]];
						}
					}
				}
				$counter = 0;
				continue;
			}

			[$currentModeArr, $csvFieldRecordArr] = $this->formatCsvArray(
				csvHeaderData: $csvHeaderData,
				csvRecordArr: $csvRecordArr
			);

			if ($counter === 0) {
				$headerModeArr = $currentModeArr;
				$dataEncodeObj->startArray(
					objectKey: $currentModeArr[0]
				);
				$dataEncodeObj->startObject();
				foreach ($csvFieldRecordArr as $objectKey => &$objectKeyValue) {
					$dataEncodeObj->addKeyData(
						objectKey: $objectKey,
						data: $objectKeyValue
					);
				}
				$counter = 1;
				continue;
			}

			if ($headerModeArr === $currentModeArr) {
				$dataEncodeObj->endObject();
				$dataEncodeObj->startObject();
			} else {
				$_headerModeArr = [];
				$headerModeCount = count(
					value: $headerModeArr
				);
				$currentModeCount = count(
					value: $currentModeArr
				);

				for (
					$index = 0;
					$index < $currentModeCount;
					$index++
				) {
					if (
						!isset($headerModeArr[$index])
						|| ($headerModeArr[$index] !== $currentModeArr[$index])
					) {
						break;
					}
					$_headerModeArr[$index] = $currentModeArr[$index];
				}
				if ($currentModeCount < $headerModeCount) {
					for ($_i = $currentModeCount; $_i < $headerModeCount; $_i++) {
						$dataEncodeObj->endObject();
						$dataEncodeObj->endArray();
					}
					$dataEncodeObj->endObject();
					$dataEncodeObj->startObject();
				}
				if ($index < $currentModeCount) {
					for ($_i = $index; $_i < $headerModeCount; $_i++) {
						$dataEncodeObj->endObject();
						$dataEncodeObj->endArray();
					}
					for ($_i = $index; $_i < $currentModeCount; $_i++) {
						$_headerModeArr[$_i] = $currentModeArr[$_i];
						$dataEncodeObj->startArray(
							objectKey: $currentModeArr[$_i]
						);
						$dataEncodeObj->startObject();
					}
				}
				$headerModeArr = $_headerModeArr;
			}
			foreach ($csvFieldRecordArr as $objectKey => &$objectKeyValue) {
				$dataEncodeObj->addKeyData(
					objectKey: $objectKey,
					data: $objectKeyValue
				);
			}
		}
		$dataEncodeObj->endObject();
		$json = $dataEncodeObj->getData();
		$dataEncodeObj = null;
		$json = substr(
			string: $json,
			offset: 7,
			length: (strlen($json)-8)
		);

		return $json;
	}

	/**
	 * Format CSV Payload
	 *
	 * @param array $csvHeaderData
	 * @param array $csvRecordArr
	 *
	 * @return array
	 */
	public function formatCsvArray(
		$csvHeaderData,
		$csvRecordArr
	): array {
		$csvFieldRecordArr = [];
		$currentModeArr = explode(
			':',
			$csvRecordArr[0]
		);

		foreach ($currentModeArr as $currentMode) {
			if (!isset($csvHeaderData[$currentMode])) {
				return [];
			}
			$csvHeaderData = &$csvHeaderData[$currentMode];
		}

		if (!isset($csvHeaderData['__column__'])) {
			throw new \Exception(
				message: json_encode(
					value: [$currentModeArr,$csvHeaderData]
				),
				code: HttpStatus::$BadRequest
			);
		}

		foreach ($csvHeaderData['__column__'] as $field => $column) {
			if (!isset($csvRecordArr[$column])) {
				return [];
			}
			$csvFieldRecordArr[$field] = $csvRecordArr[$column];
		}
		return [$currentModeArr, $csvFieldRecordArr];
	}
}
