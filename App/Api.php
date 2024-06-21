<?php
namespace Microservices\App;

use Microservices\App\Constants;
use Microservices\App\Env;
use Microservices\App\HttpRequest;
use Microservices\App\HttpResponse;
use Microservices\App\Logs;

/**
 * Class to initialize api HTTP request
 *
 * This class process the api request
 *
 * @category   API
 * @package    Microservices
 * @author     Ramesh Narayan Jangid
 * @copyright  Ramesh Narayan Jangid
 * @version    Release: @1.0.0@
 * @since      Class available since Release 1.0.0
 */
class Api
{
    /**
     * Route matched for processing before payload was collected.
     * 
     * @var boolean
     */
    private $beforePayload = null;

    /**
     * Initialize
     *
     * @return boolean
     */
    public function init()
    {
        if (HttpResponse::isSuccess()) HttpRequest::init();
        if (HttpResponse::isSuccess()) HttpRequest::loadToken();
        if (HttpResponse::isSuccess()) HttpRequest::initSession();
        if (HttpResponse::isSuccess()) HttpRequest::parseRoute();

        return HttpResponse::isSuccess();
    }

    /**
     * Process
     *
     * @return boolean
     */
    public function process()
    {
        // Check & Process Upload
        if (HttpResponse::isSuccess()) {
            if ($this->processBeforePayload()) {
                return HttpResponse::isSuccess();
            }    
        }

        if (!($success = HttpResponse::isSuccess())) {
            return $success;
        }

        // Load Payloads
        if (!Env::$isConfigRequest) {
            HttpRequest::loadPayload();
        }

        $class = null;
        switch (Constants::$REQUEST_METHOD) {
            case Constants::$GET:
                $class = __NAMESPACE__ . '\\Read';
                break;
            case Constants::$POST:
            case Constants::$PUT:
            case Constants::$PATCH:
            case Constants::$DELETE:
                $class = __NAMESPACE__ . '\\Write';
                break;
        }

        if (HttpResponse::isSuccess() && !is_null($class)) {
            $api = new $class();
            if ($api->init()) {
                $api->process();
            }
        }

        // Check & Process Cron / ThirdParty calls.
        if (HttpResponse::isSuccess()) {
            $this->processAfterPayload();
        }

        return HttpResponse::isSuccess();
    }

    /**
     * Miscellaneous Functionality Before Collecting Payload
     *
     * @return boolean
     */
    private function processBeforePayload()
    {
        $class = null;

        switch (HttpRequest::$routeElements[0]) {
            case 'custom':
                $class = __NAMESPACE__ . '\\CustomApi';
                break;
            case 'upload':
                $class = __NAMESPACE__ . '\\Upload';
                break;
            case 'thirdParty':
                $class = __NAMESPACE__ . '\\ThirdParty';
                break;
            case 'cache':
                $class = __NAMESPACE__ . '\\CacheHandler';
                break;
        }

        $foundClass = false;
        if (!empty($class)) {
            $this->beforePayload = true;
            $api = new $class();
            if ($api->init()) {
                $api->process();
            }
            $foundClass = true;
        }

        return $foundClass;
    }

    /**
     * Miscellaneous Functionality After Collecting Payload
     *
     * @return boolean
     */
    private function processAfterPayload()
    {
        return HttpResponse::isSuccess();
    }
}
