<?php
namespace Microservices\App;

use Microservices\App\Constants;
use Microservices\App\Common;
use Microservices\App\Env;
use Microservices\public_html\Supplement\ThirdParty\ThirdPartyInterface;

/**
 * Class to initiate custom API's
 *
 * @category   Third party API's
 * @package    Microservices
 * @author     Ramesh Narayan Jangid
 * @copyright  Ramesh Narayan Jangid
 * @version    Release: @1.0.0@
 * @since      Class available since Release 1.0.0
 */
class ThirdParty
{
    /**
     * @var null|ThirdPartyInterface
     */
    private $api = null;

    /**
     * Microservices Collection of Common Objects
     *
     * @var null|Common
     */
    private $c = null;

    /**
     * Constructor
     *
     * @param Common $common
     */
    public function __construct(&$common)
    {
        $this->c = &$common;
    }

    /**
     * Initialize
     *
     * @return boolean
     */
    public function init()
    {
        return true;
    }

    /**
     * Process
     *
     * @return boolean
     */
    public function process()
    {
        $class = 'Microservices\\public_html\\Supplement\\ThirdParty\\' . ucfirst($this->c->httpRequest->routeElements[1]);

        $this->api = new $class($this->c);
        if ($this->api->init()) {
            $this->api->process();
        }

        return true;
    }
}
