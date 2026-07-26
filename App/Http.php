<?php

/**
 * HTTP Class
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

use Microservices\App\Constant;
use Microservices\App\HttpRequest;
use Microservices\App\HttpResponse;

/**
 * HTTP Class
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
	 * Microservices HTTP request
	 *
	 * @var null|HttpRequest
	 */
	public $httpRequestObj = null;

	/**
	 * Microservices HTTP response
	 *
	 * @var null|HttpResponse
	 */
	public $httpResponseObj = null;

	/**
	 * HTTP request data
	 *
	 * @var null|array
	 */
	public $httpReqData = null;

	/**
	 * Constructor
	 *
	 * @param array $httpReqData
	 */
	public function __construct(
		&$httpReqData
	) {
		$this->httpReqData = &$httpReqData;
	}

	/**
	 * Initialize
	 *
	 * @return bool
	 */
	public function init(): bool
	{
		$this->httpRequestObj = new HttpRequest(
			httpObj: $this
		);
		$this->httpResponseObj = new HttpResponse(
			httpObj: $this
		);

		if ($this->httpRequestObj->isPrivateRequest) {
			$this->httpRequestObj->ROUTES_DIR = Constant::$ROUTES_PRIVATE_DIR;
			$this->httpRequestObj->QUERIES_DIR = Constant::$QUERIES_PRIVATE_DIR;

			$this->httpResponseObj->HTML_DIR = Constant::$HTML_PRIVATE_DIR;
			$this->httpResponseObj->PHP_DIR = Constant::$PHP_PRIVATE_DIR;
			$this->httpResponseObj->XSLT_DIR = Constant::$XSLT_PRIVATE_DIR;
		} else {
			$this->httpRequestObj->ROUTES_DIR = Constant::$ROUTES_PUBLIC_DIR;
			$this->httpRequestObj->QUERIES_DIR = Constant::$QUERIES_PUBLIC_DIR;

			$this->httpResponseObj->HTML_DIR = Constant::$HTML_PUBLIC_DIR;
			$this->httpResponseObj->PHP_DIR = Constant::$PHP_PUBLIC_DIR;
			$this->httpResponseObj->XSLT_DIR = Constant::$XSLT_PUBLIC_DIR;
		}

		return true;
	}

	/**
	 * Initialize request
	 *
	 * @return bool
	 */
	public function initRequest(): void
	{
		$this->httpRequestObj->init();
	}

	/**
	 * Initialize response
	 *
	 * @return bool
	 */
	public function initResponse(): void
	{
		$this->httpResponseObj->init();
	}
}
