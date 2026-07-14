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
	public $requestObj = null;

	/**
	 * Microservices HTTP response
	 *
	 * @var null|HttpResponse
	 */
	public $responseObj = null;

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
		$this->requestObj = new HttpRequest(
			httpObj: $this
		);
		$this->responseObj = new HttpResponse(
			httpObj: $this
		);

		if ($this->requestObj->isPrivateRequest) {
			$this->requestObj->ROUTES_DIR = Constant::$ROUTES_PRIVATE_DIR;
			$this->requestObj->QUERIES_DIR = Constant::$QUERIES_PRIVATE_DIR;

			$this->responseObj->HTML_DIR = Constant::$HTML_PRIVATE_DIR;
			$this->responseObj->PHP_DIR = Constant::$PHP_PRIVATE_DIR;
			$this->responseObj->XSLT_DIR = Constant::$XSLT_PRIVATE_DIR;
		} else {
			$this->requestObj->ROUTES_DIR = Constant::$ROUTES_PUBLIC_DIR;
			$this->requestObj->QUERIES_DIR = Constant::$QUERIES_PUBLIC_DIR;

			$this->responseObj->HTML_DIR = Constant::$HTML_PUBLIC_DIR;
			$this->responseObj->PHP_DIR = Constant::$PHP_PUBLIC_DIR;
			$this->responseObj->XSLT_DIR = Constant::$XSLT_PUBLIC_DIR;
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
		$this->requestObj->init();
	}

	/**
	 * Initialize response
	 *
	 * @return bool
	 */
	public function initResponse(): void
	{
		$this->responseObj->init();
	}
}
