<?php

/**
 * Initialize Custom API
 * php version 8.3
 *
 * @category  Custom
 * @package   Microservices
 * @author    Ramesh N Jangid <polygon.co.in@gmail.com>
 * @copyright 2025 Ramesh N Jangid
 * @license   MIT https://opensource.org/license/mit
 * @link      https://github.com/polygoncoin/Microservices
 * @since     Class available since Release 1.0.0
 */

namespace Microservices\App;

use Microservices\App\Common;
use Microservices\public_html\Supplement\Custom\CustomInterface;

/**
 * Custom API
 * php version 8.3
 *
 * @category  CustomAPI
 * @package   Microservices
 * @author    Ramesh N Jangid <polygon.co.in@gmail.com>
 * @copyright 2025 Ramesh N Jangid
 * @license   MIT https://opensource.org/license/mit
 * @link      https://github.com/polygoncoin/Microservices
 * @since     Class available since Release 1.0.0
 */
class Custom
{
    /**
     * Custom API object
     *
     * @var null|CustomInterface
     */
    private $customApi = null;

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
    }

    /**
     * Initialize
     *
     * @return bool
     */
    public function init(): bool
    {
        $class = 'Microservices\\public_html\\Supplement\\Custom\\'
            . ucfirst(string: $this->api->req->rParser->routeElements[1]);

        $this->customApi = new $class($this->api);

        return $this->customApi->init();
    }

    /**
     * Process
     *
     * @param string $function Function
     * @param array  $payload  Payload
     *
     * @return array
     */
    public function process($function, $payload): array
    {
        return $this->customApi->$function($payload);
    }
}
