<?php
namespace Microservices\App;

use Microservices\App\HttpResponse;
use Microservices\Custom\CustomApi;

/**
 * Class to initiate custom API's
 *
 * @category   Custom API's
 * @package    Microservices
 * @author     Ramesh Narayan Jangid
 * @copyright  Ramesh Narayan Jangid
 * @version    Release: @1.0.0@
 * @since      Class available since Release 1.0.0
 */
class Custom
{
    /**
     * Initialize
     *
     * @return boolean
     */
    public function init()
    {
        return HttpResponse::isSuccess();
    }

    /**
     * Process
     *
     * @return boolean
     */
    public function process()
    {
        $api = new CustomApi();
        if ($api->init()) {
            $api->process();
        }

        return HttpResponse::isSuccess();
    }
}
