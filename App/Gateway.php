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
	private $http = null;

	/**
	 * Constructor
	 *
	 * @param Http $http
	 */
	public function __construct(
		Http &$http
	) {
		$this->http = &$http;
	}

	/**
	 * Initialize
	 *
	 * @return bool
	 */
	public function init(): bool
	{
		if ($this->http->req->isPrivateRequest) {
			$this->http->req->auth->loadUserData();
			CommonFunction::checkPrivateRequestCidr(http: $this->http);

			$this->rateLimitRequest();
		}

		return true;
	}

	/**
	 * Rate Limit request
	 *
	 * @return void
	 */
	private function rateLimitRequest(): void
	{
		if ($this->http->req->isPrivateRequest) {
			// IP Rate Limiting
			$this->rateLimitIp();

			// Customer Rate Limiting
			$this->rateLimitCustomer();

			// Group Rate Limiting
			$this->rateLimitGroup();

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
		if (
			!CommonFunction::isEnabled(
				http: $this->http,
				feature: 'customer_enabled_rate_limiting_for_customer'
			)
			|| empty($this->http->req->s['customerData']['customer_rate_limit_max_request'])
			|| empty($this->http->req->s['customerData']['customer_rate_limit_max_request_window'])
		) {
			return;
		}

		$rateLimitCustomerPrefix = Env::$rateLimitCustomerPrefix;
		$rateLimitMaxRequest =
				$this->http->req->s['customerData']['customer_rate_limit_max_request'];
		$rateLimitMaxRequestWindow =
				$this->http->req->s['customerData']['customer_rate_limit_max_request_window'];
		$rateLimitKey = $this->http->req->customerId;

		$this->http->req->rateLimiter->checkRateLimit(
			rateLimitPrefix: $rateLimitCustomerPrefix,
			rateLimitMaxRequest: $rateLimitMaxRequest,
			rateLimitMaxRequestWindow: $rateLimitMaxRequestWindow,
			rateLimitKey: $rateLimitKey
		);
	}

	/**
	 * Rate Limit Customer Group
	 *
	 * @return void
	 */
	private function rateLimitGroup(): void
	{
		if (
			!CommonFunction::isEnabled(
				http: $this->http,
				feature: 'customer_enabled_rate_limiting_for_customer_user_group'
			)
			|| empty($this->http->req->s['userData']['customer_user_rate_limit_max_request'])
			|| empty($this->http->req->s['userData']['customer_user_rate_limit_max_request_window'])
		) {
			return;
		}

		$rateLimitGroupPrefix =
			Env::$rateLimitGroupPrefix;
		$rateLimitMaxRequest =
			$this->http->req->s['userData']['customer_user_rate_limit_max_request'];
		$rateLimitMaxRequestWindow =
			$this->http->req->s['userData']['customer_user_rate_limit_max_request_window'];
		$rateLimitKey = $this->http->req->customerId . ':'
			. $this->http->req->customerUserId;

		$this->http->req->rateLimiter->checkRateLimit(
			rateLimitPrefix: $rateLimitGroupPrefix,
			rateLimitMaxRequest: $rateLimitMaxRequest,
			rateLimitMaxRequestWindow: $rateLimitMaxRequestWindow,
			rateLimitKey: $rateLimitKey
		);
	}

	/**
	 * Rate Limit Customer Group User
	 *
	 * @return void
	 */
	private function rateLimitUser(): void
	{
		if (
			!CommonFunction::isEnabled(
				http: $this->http,
				feature: 'customer_enabled_rate_limiting_for_user'
			)
			|| empty($this->http->req->s['userData']['customer_user_rate_limit_max_request'])
			|| empty($this->http->req->s['userData']['customer_user_rate_limit_max_request_window'])
		) {
			return;
		}

		$rateLimitUserPrefix = Env::$rateLimitUserPrefix;
		$rateLimitMaxRequest =
			$this->http->req->s['userData']['customer_user_rate_limit_max_request'];
		$rateLimitMaxRequestWindow =
			$this->http->req->s['userData']['customer_user_rate_limit_max_request_window'];
		$rateLimitKey = $this->http->req->customerId . ':'
			. $this->http->req->customerUserId;

		$this->http->req->rateLimiter->checkRateLimit(
			rateLimitPrefix: $rateLimitUserPrefix,
			rateLimitMaxRequest: $rateLimitMaxRequest,
			rateLimitMaxRequestWindow: $rateLimitMaxRequestWindow,
			rateLimitKey: $rateLimitKey
		);
	}

	/**
	 * Rate Limit Customer Group User request Delay
	 *
	 * @return void
	 */
	private function rateLimitUserRequest(): void
	{
		if (
			!CommonFunction::isEnabled(
				http: $this->http,
				feature: 'customer_enabled_rate_limiting_for_user_request'
			)
			|| empty($this->http->req->s['customerData']['customer_rate_limit_user_max_request'])
			|| empty($this->http->req->s['customerData']['customer_rate_limit_user_max_request_window'])
		) {
			return;
		}

		$rateLimitUserPrefix = Env::$rateLimitUserRequestPrefix;
		$rateLimitMaxRequest = $this->http->req->s['customerData']['customer_rate_limit_user_max_request'];
		$rateLimitMaxRequestWindow = $this->http->req->s['customerData']['customer_rate_limit_user_max_request_window'];
		$rateLimitKey = $this->http->req->customerId . ':'
			. $this->http->req->customerUserId;

		$this->http->req->rateLimiter->checkRateLimit(
			rateLimitPrefix: $rateLimitUserPrefix,
			rateLimitMaxRequest: $rateLimitMaxRequest,
			rateLimitMaxRequestWindow: $rateLimitMaxRequestWindow,
			rateLimitKey: $rateLimitKey
		);
	}

	/**
	 * Rate Limit request from source IP
	 *
	 * @return void
	 */
	private function rateLimitIp(): void
	{
		if (
			!CommonFunction::isEnabled(
				http: $this->http,
				feature: 'customer_enabled_rate_limiting_for_ip'
			)
		) {
			return;
		}

		$rateLimitIPPrefix = Env::$rateLimitIPPrefix;
		$customer_rate_limit_ip_max_request = $this->http->req->s['customerData']['customer_rate_limit_ip_max_request'];
		$customer_rate_limit_ip_max_request_window = $this->http->req->s['customerData']['customer_rate_limit_ip_max_request_window'];
		$rateLimitKey = $this->http->req->customerId . ':' . $this->http->httpReqData['server']['httpRequestIP'];

		$this->http->req->rateLimiter->checkRateLimit(
			rateLimitPrefix: $rateLimitIPPrefix,
			rateLimitMaxRequest: $customer_rate_limit_ip_max_request,
			rateLimitMaxRequestWindow: $customer_rate_limit_ip_max_request_window,
			rateLimitKey: $rateLimitKey
		);
	}
}
