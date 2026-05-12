<?php

/**
 * Hook
 * php version 8.3
 *
 * @category  Hook
 * @package   Microservices
 * @author    Ramesh N. Jangid (Sharma) <polygon.co.in@gmail.com>
 * @copyright © 2026 Ramesh N. Jangid (Sharma)
 * @license   MIT https://opensource.org/license/mit
 * @link      https://github.com/polygoncoin/Microservices
 * @since     Class available since Release 1.0.0
 */

namespace Microservices\www\Hook;

use Microservices\App\Http;
use Microservices\www\Hook\HookInterface;
use Microservices\www\Hook\HookTrait;

/**
 * Hook Example class
 * php version 8.3
 *
 * @category  Hook_Example
 * @package   Microservices
 * @author    Ramesh N. Jangid (Sharma) <polygon.co.in@gmail.com>
 * @copyright © 2026 Ramesh N. Jangid (Sharma)
 * @license   MIT https://opensource.org/license/mit
 * @link      https://github.com/polygoncoin/Microservices
 * @since     Class available since Release 1.0.0
 */
class Hook_Example implements HookInterface
{
	use HookTrait;

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
		return true;
	}

	/**
	 * Process
	 *
	 * @return bool
	 */
	public function process(): bool
	{
		$this->execHook();
		return true;
	}

	/**
	 * Exec Hook
	 *
	 * @return void
	 * @throws \Exception
	 */
	private function execHook(): void
	{
		// Change payload.
		$this->http->req->s['payload']['hook'] = 'Yes';
	}
}
