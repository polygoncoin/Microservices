<?php
namespace App;

use App\Read;
use App\Write;
use App\CacheHandler;
use App\Constants;
use App\HttpRequest;
use App\HttpResponse;
use App\Logs;
use App\Upload;

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
        $this->process();
    }

    /**
     * Process all functions
     *
     * @return void
     */
    public function process()
    {
        $this->setPayload();

        switch (HttpRequest::$REQUEST_METHOD) {
            case Constants::READ:
                $request = new Read();
                break;
            case Constants::CREATE:
            case Constants::UPDATE:
            case Constants::PATCH:
            case Constants::DELETE:
                $request = new Write();
                break;
        }
        $request->init();
    }

    private function setPayload()
    {
        // Check & Process Upload
        $this->beforePayloadHook();

        // Load Payloads
        HttpRequest::loadPayload();

        // Check & Process Cron / ThirdParty calls.
        $this->afterPayloadHook();
    }
    
    /**
     * Miscellaneous Functionality Before Collecting Payload
     *
     * @return void
     */
    function beforePayloadHook()
    {
        switch (HttpRequest::$routeElements[0]) {
            case 'upload':
                Upload::init();
                die;
            case 'thirdParty':
                if (
                    isset(HttpRequest::$input['uriParams']['thirdParty']) &&
                    file_exists(__DOC_ROOT__ . '/ThirdParty/' . ucfirst(HttpRequest::$input['uriParams']['thirdParty']) . '.php')
                ) {
                    eval('ThirdParty\\' . ucfirst(HttpRequest::$input['uriParams']['thirdParty']) . '::init();');
                    die;
                } else {
                    HttpResponse::return4xx(404, "Invalid third party call");
                }
            case 'cache':
                CacheHandler::init();
                die;
        }
    }

    /**
     * Miscellaneous Functionality After Collecting Payload
     *
     * @return void
     */
    function afterPayloadHook()
    {

    }
}
