<?php
namespace Microservices\App;

use Microservices\App\Constants;
use Microservices\App\Env;
use Microservices\App\HttpRequest;
use Microservices\App\HttpResponse;
use Microservices\Cron\CronApi;

/**
 * Class to initiate custom API's
 *
 * @category   Cron API's
 * @package    Microservices
 * @author     Ramesh Narayan Jangid
 * @copyright  Ramesh Narayan Jangid
 * @version    Release: @1.0.0@
 * @since      Class available since Release 1.0.0
 */
class Cron
{
    /**
     * Initialize
     *
     * @return boolean
     */
    public function init()
    {
        if (HttpResponse::isSuccess()) HttpRequest::init();

        $routeFileLocation = Constants::$DOC_ROOT . '/Config/Routes/Common/Cron/' . Constants::$REQUEST_METHOD . 'routes.php';
        if (HttpResponse::isSuccess()) HttpRequest::parseRoute($routeFileLocation);

        return HttpResponse::isSuccess();
    }

    /**
     * Process
     *
     * @return boolean
     */
    public function process()
    {
        $api = new CronApi();
        if ($api->init()) {
            $api->process();
        }

        return HttpResponse::isSuccess();
    }
}
