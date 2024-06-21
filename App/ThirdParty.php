<?php
namespace Microservices\App;

use Microservices\App\HttpResponse;
use Microservices\ThirdParty\ThirdPartyApi;

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
        $api = new ThirdPartyApi();
        if ($api->init()) {
            $api->process();
        }

        return HttpResponse::isSuccess();
    }
}
