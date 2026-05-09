<?php

/**
 * Login
 * php version 8.3
 *
 * @category  Login
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
use Microservices\App\Constant;
use Microservices\App\DbCommonFunction;
use Microservices\App\Env;
use Microservices\App\Http;
use Microservices\App\HttpStatus;
use Microservices\App\RateLimiter;
use Microservices\App\SessionHandler\Session;

/**
 * Login
 * php version 8.3
 *
 * @category  Login
 * @package   Microservices
 * @author    Ramesh N. Jangid (Sharma) <polygon.co.in@gmail.com>
 * @copyright © 2026 Ramesh N. Jangid (Sharma)
 * @license   MIT https://opensource.org/license/mit
 * @link      https://github.com/polygoncoin/Microservices
 * @since     Class available since Release 1.0.0
 */
class Login
{
	/**
	 * DB Object
	 *
	 * @var null|object
	 */
	public $dbServerObj = null;

	/**
	 * Username for login
	 *
	 * @var null|string
	 */
	public $username = null;

	/**
	 * Password for login
	 *
	 * @var null|string
	 */
	public $password = null;

	/**
	 * Payload
	 *
	 * @var array
	 */
	private $payload = [];

	/**
	 * Http Object
	 *
	 * @var null|Http
	 */
	private $http = null;

	/**
	 * Constructor
	 *
	 * @param Http $http
	 */
	public function __construct(Http &$http)
	{
		$this->http = &$http;
	}

	/**
	 * Initialize
	 *
	 * @return bool
	 */
	public function init(): bool
	{
		$this->http->req->loadCustomerDetails();

		return true;
	}

	/**
	 * Process
	 *
	 * @return bool
	 * @throws \Exception
	 */
	public function process(): bool
	{
		// Check request method is POST
		if ($this->http->httpReqDetails['server']['httpMethod'] !== Constant::$POST) {
			throw new \Exception(
				message: 'Invalid request method',
				code: HttpStatus::$NotFound
			);
		}

		$this->loadPayload();
		$this->loadUserDetails();
		$this->validateRequestIp();
		$this->validatePassword();

		if (Env::$enableRateLimitAtUsersPerIpLevel) {
			$rateLimiter = new RateLimiter();
			$result = $rateLimiter->check(
				prefix: Env::$rateLimitUsersPerIpPrefix,
				maxRequest: Env::$rateLimitUsersPerIpMaxUsers,
				secondsWindow: Env::$rateLimitUsersPerIpMaxUsersWindow,
				key: $this->http->httpReqDetails['server']['httpRequestIP']
			);
			if ($result['allowed']) {
				// Process the request
			} else {
				// Return 429 Too Many Request
				throw new \Exception(
					message: $result['resetAt'] - Env::$timestamp,
					code: HttpStatus::$TooManyRequest
				);
			}
		}

		switch (Env::$authMode) {
			case 'Token':
				$this->outputTokenDetails();
				break;
			case 'Session':
				$this->startSession();
				break;
		}

		return true;
	}

	/**
	 * Function to load Payload
	 *
	 * @return void
	 * @throws \Exception
	 */
	private function loadPayload(): void
	{
		// Check request method is POST
		if ($this->http->httpReqDetails['server']['httpMethod'] !== Constant::$POST) {
			throw new \Exception(
				message: 'Invalid request method',
				code: HttpStatus::$NotFound
			);
		}

		$this->http->req->loadPayload();
		$this->payload = $this->http->req->dataDecode->get();

		// Check for required conditions variables
		foreach (['username', 'password'] as $value) {
			if (
				!isset($this->payload[$value])
				|| empty($this->payload[$value])
			) {
				throw new \Exception(
					message: 'Missing required parameters',
					code: HttpStatus::$NotFound
				);
			} else {
				$this->$value = $this->payload[$value];
			}
		}
	}

	/**
	 * Function to load user details from cache
	 *
	 * @return void
	 * @throws \Exception
	 */
	private function loadUserDetails(): void
	{
		$cID = $this->http->req->cID;
		$customerUserKey = CacheServerKey::customerUsername(
			cID: $cID,
			username: $this->payload['username']
		);
		// Redis - one can find the userID from customer username
		if (!$this->cacheExists(key: $customerUserKey)) {
			throw new \Exception(
				message: 'Invalid credentials',
				code: HttpStatus::$Unauthorized
			);
		}
		$uDetails = json_decode(
			json: $this->getCache(
				key: $customerUserKey
			),
			associative: true
		);
		if (
			empty($uDetails['id'])
			|| empty($uDetails['id'])
		) {
			throw new \Exception(
				message: 'Invalid credentials',
				code: HttpStatus::$Unauthorized
			);
		}
		$this->http->req->s['uDetails'] = $uDetails;
		$this->http->req->uID = $uDetails['id'];
		$this->http->req->gID = $uDetails['group_id'];
	}

	/**
	 * Function to validate source ip
	 *
	 * @return void
	 * @throws \Exception
	 */
	private function validateRequestIp(): void
	{
		$ipNumber = ip2long(ip: $this->http->httpReqDetails['server']['httpRequestIP']);

		$cCidrKey = CacheServerKey::customerCidr(
			cID: $this->http->req->cID
		);
		$gCidrKey = CacheServerKey::customerGroupCidr(
			cID: $this->http->req->cID,
			gID: $this->http->req->gID
		);
		$uCidrKey = CacheServerKey::customerUserCidr(
			cID: $this->http->req->cID,
			uID: $this->http->req->uID
		);
		$cidrChecked = false;
		foreach ([$cCidrKey, $gCidrKey, $uCidrKey] as $key) {
			if (!$cidrChecked) {
				$cidrChecked = CommonFunction::checkCacheCidr(
					IP: $this->http->httpReqDetails['server']['httpRequestIP'],
					againstCacheKey: $key
				);
			}
		}
	}

	/**
	 * Validates password from its hash present in cache
	 *
	 * @return void
	 * @throws \Exception
	 */
	private function validatePassword(): void
	{
		$rateLimiter = new RateLimiter();
		$result = $rateLimiter->check(
			prefix: Env::$rateLimitUserLoginPrefix,
			maxRequest: Env::$rateLimitMaxUserLoginRequest,
			secondsWindow: Env::$rateLimitMaxUserLoginRequestWindow,
			key: $this->http->httpReqDetails['server']['httpRequestIP'] . $this->username
		);
		if ($result['allowed']) {
			// Process the request
		} else {
			// Return 429 Too Many Request
			throw new \Exception(
				message: $result['resetAt'] - Env::$timestamp,
				code: HttpStatus::$TooManyRequest
			);
		}
		// get hash from cache and compares with password
		if (
			!password_verify(
				password: $this->password,
				hash: $this->http->req->s['uDetails']['password_hash']
			)
		) {
			throw new \Exception(
				message: 'Invalid credentials',
				code: HttpStatus::$Unauthorized
			);
		}
	}

	/**
	 * Generates token
	 *
	 * @return array
	 */
	private function generateToken(): array
	{
		//generates a crypto-secure 64 characters long
		while (true) {
			$token = bin2hex(string: random_bytes(length: 32));

			if (
				!$this->cacheExists(
					key: CacheServerKey::token(token: $token)
				)
			) {
				$this->setCache(
					key: CacheServerKey::token(token: $token),
					value: '{}',
					expire: Constant::$TOKEN_EXPIRY_TIME
				);
				$userTokenKeyData = [
					'token' => $token,
					'timestamp' => Env::$timestamp
				];
				break;
			}
		}
		return $userTokenKeyData;
	}

	/**
	 * Outputs active/newly generated token details
	 *
	 * @return void
	 */
	private function outputTokenDetails(): void
	{
		$httpRequestHash = $this->http->httpReqDetails['httpRequestHash'];

		if (Env::$enableConcurrentLogins) {
			$userConcurrencyKey = CacheServerKey::customerUserConcurrency(
				cID: $this->http->req->cID,
				uID: $this->http->req->uID
			);

			$userConcurrencyKeyExist = false;
			$userConcurrencyKeyData = '';
			if ($this->cacheExists(key: $userConcurrencyKey)) {
				$userConcurrencyKeyExist = true;
				$userConcurrencyKeyData = $this->getCache(
					key: $userConcurrencyKey
				);
			}
		}

		$tokenFound = false;
		$tokenFoundData = [];
		$userTokenKeyData = [];

		$userTokenKey = CacheServerKey::customerUserToken(
			cID: $this->http->req->cID,
			uID: $this->http->req->uID
		);

		if ($this->cacheExists(key: $userTokenKey)) {
			$userTokenKeyData = json_decode(
				json: $this->getCache(
					key: $userTokenKey
				),
				associative: true
			);
			if (count($userTokenKeyData) > 0) {
				foreach ($userTokenKeyData as $token => $tData) {
					if ($this->cacheExists(key: CacheServerKey::token(token: $token))) {
						if (Env::$enableConcurrentLogins) {
							if (
								$tData['httpRequestHash'] === $httpRequestHash
								&& $userConcurrencyKeyExist
								&& $userConcurrencyKeyData === $token
							) {
								$timeLeft = Env::$timestamp - $tData['timestamp'];
								if ((Constant::$TOKEN_EXPIRY_TIME - $timeLeft) > 0) {
									$tokenFoundData = $tData;
									$tokenFound = true;
									continue;
								}
							}
						} else {
							if (
								$tData['httpRequestHash'] === $httpRequestHash
								&& $userConcurrencyKeyData === $token
							) {
								$timeLeft = Env::$timestamp - $tData['timestamp'];
								if ((Constant::$TOKEN_EXPIRY_TIME - $timeLeft) > 0) {
									$tokenFoundData = $tData;
									$tokenFound = true;
									continue;
								}
							}
						}
						$timeLeft = Env::$timestamp - $tData['timestamp'];
						if ((Constant::$TOKEN_EXPIRY_TIME - $timeLeft) <= 0) {
							$this->deleteCache(
								key: CacheServerKey::token(
									token: $token
								)
							);
							unset($userTokenKeyData[$token]);
						}
					} else {
						unset($userTokenKeyData[$token]);
					}
				}
				if (
					Env::$enableConcurrentLogins
					&& count($userTokenKeyData) >= Env::$maxConcurrentLogins
				) {
					throw new \Exception(
						message: 'Account already in use. '
							. 'Please try after ' . Env::$concurrentAccessInterval . ' second(s)',
						code: HttpStatus::$Conflict
					);
				}
			} else {
				$this->deleteCache(key: $userTokenKey);
			}
		}

		if (!$tokenFound) {
			$newTokenData = $this->generateToken();
			$newTokenData['httpRequestHash'] = $httpRequestHash;

			unset($this->http->req->s['uDetails']['password_hash']);
			foreach ($newTokenData as $k => $v) {
				$this->http->req->s['uDetails'][$k] = $v;
			}

			$this->setCache(
				key: CacheServerKey::token(token: $newTokenData['token']),
				value: json_encode(
					value: $this->http->req->s['uDetails']
				),
				expire: Constant::$TOKEN_EXPIRY_TIME
			);
			if (Env::$enableConcurrentLogins) {
				$userTokenKeyData[$newTokenData['token']] = $newTokenData;
			} else {
				$userTokenKeyData = [
					$newTokenData['token'] => $newTokenData
				];
			}
			$this->updateDB(userData: $userTokenKeyData);

			$tokenFoundData = &$newTokenData;
			$tokenFound = true;
		}

		if (!$tokenFound) {
			throw new \Exception(
				message: 'Unexpected error occured during login',
				code: HttpStatus::$InternalServerError
			);
		}

		$token = $tokenFoundData['token'];

		$this->setCache(
			key: $userTokenKey,
			value: json_encode(
				value: $userTokenKeyData
			),
			expire: Constant::$TOKEN_EXPIRY_TIME
		);
		if (Env::$enableConcurrentLogins) {
			$this->setCache(
				key: $userConcurrencyKey,
				value: $token,
				expire: Env::$concurrentAccessInterval
			);
		}
		$time = Env::$timestamp - $tokenFoundData['timestamp'];
		$output = [
			'Token' => $tokenFoundData['token'],
			'Expires' => date('d\ \d\a\y H\ \h\o\u\r i\ \m\i\n s\ \s\e\c', (Constant::$TOKEN_EXPIRY_TIME - $time))
		];

		$this->http->initResponse();
		$this->http->res->dataEncode->startObject();
		$this->http->res->dataEncode->addKeyData(key: 'Results', data: $output);
	}

	/**
	 * Update token details in DB for respective account
	 *
	 * @param array $userData Token Data
	 *
	 * @return void
	 */
	private function updateDB(&$userData): void
	{
		DbCommonFunction::setDbConnection($this->http->req, fetchFrom: 'Master');
		$this->dbServerObj = &DbCommonFunction::$masterDb[$this->http->req->cID];

		$this->dbServerObj->execDbQuery(
			sql: "
				UPDATE
					`{$this->http->req->s['cDetails']['usersTable']}`
				SET
					`token` = :token
				WHERE
					id = :id",
			params: [
				':token' => json_encode($userData),
				':id' => $this->http->req->s['uDetails']['id']
			]
		);
	}

	/**
	 * Outputs active/newly generated session details
	 *
	 * @return void
	 */
	private function startSession(): void
	{
		$httpRequestHash = $this->http->httpReqDetails['httpRequestHash'];

		if (Env::$enableConcurrentLogins) {
			$userConcurrencyKey = CacheServerKey::customerUserConcurrency(
				cID: $this->http->req->cID,
				uID: $this->http->req->uID
			);

			$userConcurrencyKeyExist = false;
			$userConcurrencyKeyData = '';
			if ($this->cacheExists(key: $userConcurrencyKey)) {
				$userConcurrencyKeyExist = true;
				$userConcurrencyKeyData = $this->getCache(
					key: $userConcurrencyKey
				);
			}
		}

		$sessionFound = false;
		$sessionFoundData = [];
		$userSessionKeyData = [];

		$userSessionKey = CacheServerKey::customerUserSessionID(
			cID: $this->http->req->cID,
			uID: $this->http->req->uID
		);

		if ($this->cacheExists(key: $userSessionKey)) {
			$userSessionKeyData = json_decode(
				json: $this->getCache(
					key: $userSessionKey
				),
				associative: true
			);
			if (count($userSessionKeyData) > 0) {
				foreach ($userSessionKeyData as $sessionID => $tData) {
					if (Env::$enableConcurrentLogins) {
						if (
							$tData['httpRequestHash'] === $httpRequestHash
							&& $userConcurrencyKeyExist
							&& $userConcurrencyKeyData === $sessionID
							&& $sessionID === session_id()
						) {
							$timeLeft = Env::$timestamp - $tData['sessionExpiryTimestamp'];
							if ((Constant::$TOKEN_EXPIRY_TIME - $timeLeft) > 0) {
								$sessionFoundData = $tData;
								$sessionFound = true;
								continue;
							}
						}
					} else {
						if (
							$tData['httpRequestHash'] === $httpRequestHash
							&& $sessionID === session_id()
						) {
							$timeLeft = Env::$timestamp - $tData['sessionExpiryTimestamp'];
							if ((Constant::$TOKEN_EXPIRY_TIME - $timeLeft) > 0) {
								$sessionFoundData = $tData;
								$sessionFound = true;
								continue;
							}
						}
					}
					if (isset($tData['sessionExpiryTimestamp'])) {
						$timeLeft = Env::$timestamp - $tData['sessionExpiryTimestamp'];
						if ((Constant::$TOKEN_EXPIRY_TIME - $timeLeft) <= 0) {
							Session::deleteSession(sessionID: $sessionID);
							unset($userSessionKeyData[$sessionID]);
						}
					}
				}
				if (Env::$enableConcurrentLogins) {
					if (count($userSessionKeyData) >= Env::$maxConcurrentLogins) {
						throw new \Exception(
							message: 'Account already in use. '
								. 'Please try after ' . Env::$concurrentAccessInterval . ' second(s)',
							code: HttpStatus::$Conflict
						);
					}
				}
			} else {
				$this->deleteCache(key: $userSessionKey);
			}
		}

		if (!$sessionFound) {
			Session::sessionStartReadWrite();
			$newSessionData = [
				'sessionID' => session_id(),
				'timestamp' => Env::$timestamp,
				'httpRequestHash' => $httpRequestHash,
				'sessionExpiryTimestamp' => (Env::$timestamp + Constant::$TOKEN_EXPIRY_TIME)
			];

			unset($this->http->req->s['uDetails']['password_hash']);
			foreach ($newSessionData as $k => $v) {
				$this->http->req->s['uDetails'][$k] = $v;
			}

			$_SESSION = $this->http->req->s['uDetails'];

			if (Env::$enableConcurrentLogins) {
				$userSessionKeyData[$newSessionData['sessionID']] = $newSessionData;
			} else {
				$userSessionKeyData = [
					$newSessionData['sessionID'] => $newSessionData
				];
			}
			$this->updateDB(userData: $userSessionKeyData);

			$sessionFoundData = &$newSessionData;
			$sessionFound = true;
		}

		if (!$sessionFound) {
			throw new \Exception(
				message: 'Unexpected error occured during login',
				code: HttpStatus::$InternalServerError
			);
		}

		$sessionID = $sessionFoundData['sessionID'];

		$this->setCache(
			key: $userSessionKey,
			value: json_encode(
				value: $userSessionKeyData
			),
			expire: Constant::$TOKEN_EXPIRY_TIME
		);
		if (Env::$enableConcurrentLogins) {
			$this->setCache(
				key: $userConcurrencyKey,
				value: $sessionID,
				expire: Env::$concurrentAccessInterval
			);
		}
		$time = Env::$timestamp - $sessionFoundData['sessionExpiryTimestamp'];
		$output = [
			'sessionID' => $sessionFoundData['sessionID'],
			'Expires' => date('d\ \d\a\y H\ \h\o\u\r i\ \m\i\n s\ \s\e\c', (Constant::$TOKEN_EXPIRY_TIME - $time))
		];

		$this->http->initResponse();
		$this->http->res->dataEncode->startObject();
		$this->http->res->dataEncode->addKeyData(key: 'Results', data: $output);
	}

	/**
	 * Checks if cache key exist
	 *
	 * @param string $key Cache key
	 *
	 * @return mixed
	 */
	private function cacheExists($key) {
		return DbCommonFunction::$gCacheServer->cacheExists(key: $key);
	}

	/**
	 * Get cache on basis of key
	 *
	 * @param string $key Cache key
	 *
	 * @return mixed
	 */
	private function getCache($key) {
		return DbCommonFunction::$gCacheServer->getCache(key: $key);
	}

	/**
	 * Set cache on basis of key
	 *
	 * @param string $key    Cache key
	 * @param string $value  Cache value
	 * @param int    $expire Seconds to expire. Default 0 - doesn't expire
	 *
	 * @return mixed
	 */
	private function setCache($key, $value, $expire = 0) {
		return DbCommonFunction::$gCacheServer->setCache(
			key: $key,
			value: $value,
			expire: $expire
		);
	}

	/**
	 * Delete basis of key
	 *
	 * @param string $key Cache key
	 *
	 * @return mixed
	 */
	private function deleteCache($key) {
		return DbCommonFunction::$gCacheServer->deleteCache(key: $key);
	}
}
