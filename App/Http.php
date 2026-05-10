<?php

/**
 * Http Class
 * php version 8.3
 *
 * @category  Http
 * @package   Microservices
 * @author    Ramesh N. Jangid (Sharma) <polygon.co.in@gmail.com>
 * @copyright © 2026 Ramesh N. Jangid (Sharma)
 * @license   MIT https://opensource.org/license/mit
 * @link      https://github.com/polygoncoin/Microservices
 * @since     Class available since Release 1.0.0
 */

namespace Microservices\App;

use Microservices\App\HttpRequest;
use Microservices\App\HttpResponse;

/**
 * Http Class
 * php version 8.3
 *
 * @category  Http
 * @package   Microservices
 * @author    Ramesh N. Jangid (Sharma) <polygon.co.in@gmail.com>
 * @copyright © 2026 Ramesh N. Jangid (Sharma)
 * @license   MIT https://opensource.org/license/mit
 * @link      https://github.com/polygoncoin/Microservices
 * @since     Class available since Release 1.0.0
 */
class Http
{
	/**
	 * Microservices http Request
	 *
	 * @var null|HttpRequest
	 */
	public $req = null;

	/**
	 * Microservices http Response
	 *
	 * @var null|HttpResponse
	 */
	public $res = null;

	/**
	 * Http Request Detail
	 *
	 * @var null|array
	 */
	public $httpReqDetailArr = null;

	/**
	 * Initialize
	 *
	 * @param array $httpReqDetailArr Http Request Detail
	 *
	 * @return void
	 */
	public function init(&$httpReqDetailArr): void
	{
		$this->httpReqDetailArr = &$httpReqDetailArr;
		$this->req = new HttpRequest(http: $this);
		$this->res = new HttpResponse(http: $this);
	}

	/**
	 * Initialize Request
	 *
	 * @return bool
	 */
	public function initRequest(): void
	{
		$this->req->init();
	}

	/**
	 * Initialize Response
	 *
	 * @return bool
	 */
	public function initResponse(): void
	{
		$this->res->init();
	}
}
