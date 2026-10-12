<?php

/**
 * Gateway
 * php version 8.3
 *
 * @category  Gateway
 * @package   Microservices
 * @author    Ramesh N. Jangid (Sharma) <polygon.co.in@gmail.com>
 * @copyright © 2026 Ramesh N. Jangid (Sharma)
 * @license   MIT https://opensource.org/license/mit
 * @link      https://github.com/polygoncoin/Microservices
 * @since     Class available since Release 1.0.0
 */

namespace Microservices\App;

use Microservices\App\CommonFunction;
use Microservices\App\Constant;
use Microservices\App\Env;
use Microservices\App\Http;

/**
 * Gateway - contains checks like IP and Rate Limiting functions
 * php version 8.3
 *
 * @category  Gateway
 * @package   Microservices
 * @author    Ramesh N. Jangid (Sharma) <polygon.co.in@gmail.com>
 * @copyright © 2026 Ramesh N. Jangid (Sharma)
 * @license   MIT https://opensource.org/license/mit
 * @link      https://github.com/polygoncoin/Microservices
 * @since     Class available since Release 1.0.0
 */
class Gateway
{
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
	}

	/**
	 * Initialize
	 * 
	 * @return bool
	 */
	public function init(): bool
	{
		if ($this->httpObject->httpRequestObject->isPrivateRequest) {
			$this->httpObject->httpRequestObject->authObject->loadUserData();
			CommonFunction::checkPrivateRequestCidr(
				httpObject: $this->httpObject
			);

			$this->rateLimitRequest();
		}

		return Constant::$TRUE;
	}

	/**
	 * Rate Limit request
	 * 
	 * @return void
	 */
	private function rateLimitRequest(): void
	{
		if ($this->httpObject->httpRequestObject->isPrivateRequest) {
			// IP Rate Limiting
			$this->rateLimitIp();

			// Customer Rate Limiting
			$this->rateLimitCustomer();

			// User Rate Limiting
			$this->rateLimitUser();

			// User Rate Limiting request Delay
			$this->rateLimitUserRequest();
		}
	}

	/**
	 * Rate Limit Customer
	 * 
	 * @return void
	 */
	private function rateLimitCustomer(): void
	{
		$this->httpObject->rateLimiterObject->rateLimitFeature(
			feature: 'CUSTOMER'
		);
	}

	/**
	 * Rate Limit Customer Group User
	 * 
	 * @return void
	 */
	private function rateLimitUser(): void
	{
		$this->httpObject->rateLimiterObject->rateLimitFeature(
			feature: 'USER'
		);
	}

	/**
	 * Rate Limit Customer Group User request Delay
	 * 
	 * @return void
	 */
	private function rateLimitUserRequest(): void
	{
		$this->httpObject->rateLimiterObject->rateLimitFeature(
			feature: 'USER_ROUTE'
		);
	}

	/**
	 * Rate Limit request from source IP
	 * 
	 * @return void
	 */
	private function rateLimitIp(): void
	{
		$this->httpObject->rateLimiterObject->rateLimitFeature(
			feature: 'CUSTOMER_IP'
		);
	}
}
