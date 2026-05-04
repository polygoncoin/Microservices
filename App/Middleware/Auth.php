<?php

/**
 * Middleware
 * php version 8.3
 *
 * @category  Middleware
 * @package   Microservices
 * @author    Ramesh N. Jangid (Sharma) <polygon.co.in@gmail.com>
 * @copyright © 2026 Ramesh N. Jangid (Sharma)
 * @license   MIT https://opensource.org/license/mit
 * @link      https://github.com/polygoncoin/Microservices
 * @since     Class available since Release 1.0.0
 */

namespace Microservices\App\Middleware;

use Microservices\App\CacheServerKey;
use Microservices\App\DbCommonFunction;
use Microservices\App\Env;
use Microservices\App\Http;
use Microservices\App\HttpStatus;

/**
 * Class handling details for Auth middleware
 * php version 8.3
 *
 * @category  Auth_Middleware
 * @package   Microservices
 * @author    Ramesh N. Jangid (Sharma) <polygon.co.in@gmail.com>
 * @copyright © 2026 Ramesh N. Jangid (Sharma)
 * @license   MIT https://opensource.org/license/mit
 * @link      https://github.com/polygoncoin/Microservices
 * @since     Class available since Release 1.0.0
 */
class Auth
{
	/**
	 * Http Object
	 *
	 * @var null|Http
	 */
	private $http = null;

	/**
	 * Request ID
	 *
	 * @var null|int
	 */
	private $requestID = null;

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
	 * Load User Details
	 *
	 * @return void
	 * @throws \Exception
	 */
	public function loadUserDetails(): void
	{
		if (isset($this->http->req->s['uDetails'])) {
			return;
		}

		if (
			isset($_SESSION)
			&& isset($_SESSION['id'])
		) {
			$this->http->req->s['uDetails'] = $_SESSION;
			$this->http->req->s['token'] = 'sessions';
			$this->http->req->uID = $_SESSION['id'];
		} elseif (
			($this->http->httpReqDetails['header']['tokenHeader'] !== null)
		) {
			if (
				!preg_match(
					pattern: '/Bearer\s(\S+)/',
					subject: $this->http->httpReqDetails['header']['tokenHeader'],
					matches: $matches
				)
			) {
				throw new \Exception(
					message: 'Token missing',
					code: HttpStatus::$BadRequest
				);
			}
			$this->http->req->s['token'] = $matches[1];
			$tokenKey = CacheServerKey::token(
				token: $this->http->req->s['token']
			);
			if (
				!DbCommonFunction::$gCacheServer->cacheExists(
					key: $tokenKey
				)
			) {
				throw new \Exception(
					message: 'Token expired',
					code: HttpStatus::$BadRequest
				);
			}
			$this->http->req->s['uDetails'] = json_decode(
				json: DbCommonFunction::$gCacheServer->getCache(
					key: $tokenKey
				),
				associative: true
			);
			$this->http->req->uID = $this->http->req->s['uDetails']['id'];
			$this->http->req->gID = $this->http->req->s['uDetails']['group_id'];

			if (Env::$enableConcurrentLogins) {
				$userConcurrencyKey = CacheServerKey::customerUserConcurrency(
					cID: $this->http->req->cID,
					uID: $this->http->req->uID
				);
				if (DbCommonFunction::$gCacheServer->cacheExists(key: $userConcurrencyKey)) {
					$userConcurrencyKeyData = DbCommonFunction::$gCacheServer->getCache(
						key: $userConcurrencyKey
					);
					if ($userConcurrencyKeyData !== $this->http->req->s['token']) {
						throw new \Exception(
							message: 'Account already in use. '
								. 'Please try after ' . Env::$concurrentAccessInterval . ' second(s)',
							code: HttpStatus::$Conflict
						);
					}
				} else {
					$this->setCache(
						key: $userConcurrencyKey,
						value: $this->http->req->s['token'],
						expire: Env::$concurrentAccessInterval
					);
				}
			} else {
				if ($this->http->req->s['uDetails']['httpRequestHash'] !== $this->http->httpReqDetails['httpRequestHash']) {
					throw new \Exception(
						message: 'Token not supported from this Browser/Device',
						code: HttpStatus::$PreconditionFailed
					);
				}
			}
		}
	}

	/**
	 * Load User Details
	 *
	 * @return void
	 * @throws \Exception
	 */
	public function loadGroupDetails(): void
	{
		if (isset($this->http->req->s['gDetails'])) {
			return;
		}

		// Load gDetails
		if (
			empty($this->http->req->uID)
			|| empty($this->http->req->uID)
		) {
			throw new \Exception(
				message: 'Invalid session',
				code: HttpStatus::$InternalServerError
			);
		}

		$gKey = CacheServerKey::customerGroup(
			cID: $this->http->req->cID,
			gID: $this->http->req->gID
		);
		if (!DbCommonFunction::$gCacheServer->cacheExists(key: $gKey)) {
			throw new \Exception(
				message: "Cache '{$gKey}' missing",
				code: HttpStatus::$InternalServerError
			);
		}

		$this->http->req->s['gDetails'] = json_decode(
			json: DbCommonFunction::$gCacheServer->getCache(
				key: $gKey
			),
			associative: true
		);
	}
}
