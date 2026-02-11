<?php

/**
 * HTTP Response
 * php version 8.3
 *
 * @category  HTTP_Response
 * @package   Microservices
 * @author    Ramesh N Jangid <polygon.co.in@gmail.com>
 * @copyright 2025 Ramesh N Jangid
 * @license   MIT https://opensource.org/license/mit
 * @link      https://github.com/polygoncoin/Microservices
 * @since     Class available since Release 1.0.0
 */

namespace Microservices\App;

use Microservices\App\Common;
use Microservices\App\DataRepresentation\DataEncode;
use Microservices\App\Env;
use Microservices\App\HttpStatus;

/**
 * HTTP Response
 * php version 8.3
 *
 * @category  HTTP_Response
 * @package   Microservices
 * @author    Ramesh N Jangid <polygon.co.in@gmail.com>
 * @copyright 2025 Ramesh N Jangid
 * @license   MIT https://opensource.org/license/mit
 * @link      https://github.com/polygoncoin/Microservices
 * @since     Class available since Release 1.0.0
 */
class HttpResponse
{
    /**
     * Output Representation
     *
     * @var null|string
     */
    public $oRepresentation = null;

    /**
     * HTTP Status
     *
     * @var int
     */
    public $httpStatus;

    /**
     * JSON Encode object
     *
     * @var null|DataEncode
     */
    public $dataEncode = null;

    /**
     * Api common Object
     *
     * @var null|Common
     */
    private $api = null;

    /**
     * Constructor
     *
     * @param Common $api
     */
    public function __construct(Common &$api)
    {
        $this->api = &$api;
        $this->httpStatus = HttpStatus::$Ok;
        $this->oRepresentation = Env::$oRepresentation;
    }

    /**
     * Initialize
     *
     * @return void
     */
    public function init(): void
    {
        $this->dataEncode = new DataEncode(api: $this->api);
        $this->dataEncode->init();
    }
}
