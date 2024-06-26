<?php
namespace Microservices\Cron;

use Microservices\App\Constants;
use Microservices\App\Env;
use Microservices\App\HttpRequest;
use Microservices\App\HttpResponse;
use Microservices\App\Logs;
use Microservices\App\Servers\Cache\Cache;
use Microservices\App\Servers\Database\Database;

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
class CronApi
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
        $class = __NAMESPACE__ . '\\' . ucfirst(HttpRequest::$routeElements[1]);
        $api = new $class();
        if ($api->init()) {
            $api->process();
        }

        return HttpResponse::isSuccess();
    }
}
