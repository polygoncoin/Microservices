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

namespace Microservices\App;

use Microservices\App\CacheServerKey;
use Microservices\App\Constant;
use Microservices\App\Env;
use Microservices\App\Http;
use Microservices\App\HttpStatus;

/**
 * Class handling detail for Auth middleware
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
	 * Load User Data
	 * 
	 * @return void
	 * @throws \Exception
	 */
	public function loadUserData(): void
	{
		if (isset($this->httpObj->httpRequestObj->activeRequestCollection['userData'])) {
			return;
		}

		if (
			isset($_SESSION)
			&& isset($_SESSION['customer_user_id'])
		) {
			$this->httpObj->httpRequestObj->activeRequestCollection['userData'] = $_SESSION;
			$this->httpObj->httpRequestObj->activeRequestCollection['authId'] = session_id();
		} elseif (
			isset($this->httpObj->httpReqData['header']['tokenHeader'])
			&& $this->httpObj->httpReqData['header']['tokenHeader'] !== Constant::$NULL
		) {
			if (
				!preg_match(
					pattern: '/Bearer\s(\S+)/',
					subject: $this->httpObj->httpReqData['header']['tokenHeader'],
					matches: $matches
				)
			) {
				throw new \Exception(
					message: 'Token missing',
					code: HttpStatus::$BadRequest
				);
			}
			$this->httpObj->httpRequestObj->activeRequestCollection['authId'] = $matches[1];
			$tokenKey = CacheServerKey::token(
				token: $this->httpObj->httpRequestObj->activeRequestCollection['authId']
			);
			if (
				!$this->httpObj->httpRequestObj->customerCacheObj->cacheExist(
					cacheKey: $tokenKey
				)
			) {
				throw new \Exception(
					message: 'Please login',
					code: HttpStatus::$BadRequest
				);
			}
			$this->httpObj->httpRequestObj->activeRequestCollection['userData'] = $this->httpObj->httpRequestObj->customerCacheObj->cacheGet(
				cacheKey: $tokenKey
			);
		} else {
			throw new \Exception(
				message: 'Please login',
				code: HttpStatus::$BadRequest
			);
		}

		if (($this->httpObj->httpRequestObj->activeRequestCollection['userData']['authTimestamp'] + Constant::$TOKEN_EXPIRY_TIME) <= Env::$timestamp) {
			throw new \Exception(
				message: 'Login has timed out. Please login',
				code: HttpStatus::$BadRequest
			);
		}

		if ($this->httpObj->httpRequestObj->activeRequestCollection['userData']['httpRequestHash'] !== $this->httpObj->httpReqData['httpRequestHash']) {
			throw new \Exception(
				message: 'Current Browser or the Device location not matching with Browser or the Device location during Login',
				code: HttpStatus::$PreconditionFailed
			);
		}

		$this->httpObj->httpRequestObj->customerUserId = $this->httpObj->httpRequestObj->activeRequestCollection['userData']['customer_user_id'];
		$this->httpObj->httpRequestObj->customerUserGroupId = $this->httpObj->httpRequestObj->activeRequestCollection['userData']['customer_user_group_id'];
	}

	/**
	 * Load Group Data
	 * 
	 * @return void
	 * @throws \Exception
	 */
	public function loadGroupData(): void
	{
		if (isset($this->httpObj->httpRequestObj->activeRequestCollection['groupData'])) {
			return;
		}

		// Load groupData
		$groupCacheKey = CacheServerKey::customerGroup(
			customerId: $this->httpObj->httpRequestObj->customerId,
			customerUserGroupId: $this->httpObj->httpRequestObj->customerUserGroupId
		);
		if (
			!$this->httpObj->httpRequestObj->customerCacheObj->cacheExist(
				cacheKey: $groupCacheKey
			)
		) {
			throw new \Exception(
				message: "Cache '{$groupCacheKey}' missing",
				code: HttpStatus::$InternalServerError
			);
		}

		$this->httpObj->httpRequestObj->activeRequestCollection['groupData'] = $this->httpObj->httpRequestObj->customerCacheObj->cacheGet(
			cacheKey: $groupCacheKey
		);
	}
}
