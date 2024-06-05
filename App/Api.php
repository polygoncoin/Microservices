<?php
namespace App;

use App\Constants;
use App\Env;
use App\HttpRequest;
use App\HttpResponse;
use App\Logs;
use App\Read;
use App\Write;

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
     * Initialize
     *
     * @return void
     */
    public function init()
    {
        HttpRequest::init();
        HttpRequest::loadToken();
        HttpRequest::initSession();
        HttpRequest::parseRoute();

        $this->processApi();
    }

    /**
     * Process all functions
     *
     * @return void
     */
    private function processApi()
    {
        // Check & Process Upload
        if ($this->processBeforePayload()) {
            return;
        }

        // Load Payloads
        if (!Env::$isConfigRequest) {
            HttpRequest::loadPayload();
        }

        switch (Constants::$REQUEST_METHOD) {
            case Constants::$GET:
                $request = new Read();
                break;
            case Constants::$POST:
            case Constants::$PUT:
            case Constants::$PATCH:
            case Constants::$DELETE:
                $request = new Write();
                break;
        }
        $request->init();

        // Check & Process Cron / ThirdParty calls.
        $this->processAfterPayload();
    }

    /**
     * Miscellaneous Functionality Before Collecting Payload
     *
     * @return void
     */
    private function processBeforePayload()
    {
        $class = null;
        switch (HttpRequest::$routeElements[0]) {
            case 'custom':
                $class = 'Custom\\CustomApi';
                break;
            case 'upload':
                $class = 'App\\Upload';
                break;
            case 'thirdParty':
                if (
                    isset(HttpRequest::$input['uriParams']['thirdParty']) &&
                    file_exists(Constants::$DOC_ROOT . '/ThirdParty/' . ucfirst(HttpRequest::$input['uriParams']['thirdParty']) . '.php')
                ) {
                    $class = 'ThirdParty\\'.HttpRequest::$input['uriParams']['thirdParty'];
                } else {
                    HttpResponse::return4xx(404, 'Invalid third party call');
                }
                break;
            case 'cache':
                $class = 'App\\CacheHandler';
                break;
        }
        if (!empty($class)) {
            $api = new $class();
            $api->init();
            return true;
        }
        return false;
    }

    /**
     * Miscellaneous Functionality After Collecting Payload
     *
     * @return void
     */
    private function processAfterPayload()
    {

    }
}
