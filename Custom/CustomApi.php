<?php
namespace Custom;

use App\Constants;
use App\Env;
use App\HttpRequest;
use App\HttpResponse;
use App\Logs;
use App\Servers\Database\Database;

/**
 * Class to initialize api HTTP request
 *
 * This class process the api request
 *
 * @category   Custom API
 * @package    Microservices
 * @author     Ramesh Narayan Jangid
 * @copyright  Ramesh Narayan Jangid
 * @version    Release: @1.0.0@
 * @since      Class available since Release 1.0.0
 */
class CustomApi
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
     * Process all functions
     *
     * @return boolean
     */
    public function process()
    {
        if (file_exists(Constants::$DOC_ROOT . '/Custom/' . ucfirst(HttpRequest::$routeElements[1]) . '.php')) {
            $class = 'Custom\\'.ucfirst(HttpRequest::$routeElements[1]);
            $api = new $class();
            if ($api->init()) {
                $api->process();
            }
        }
        return HttpResponse::isSuccess();
    }
}
