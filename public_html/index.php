<?php
require_once __DIR__ . '/../autoload.php';

use App\Constants;

class Microservices
{
    /**
     * Start micro timestamp;
     */
    private $tsStart = null;

    /**
     * End micro timestamp;
     */
    private $tsEnd = null;

    private $jsonObj = null;

    public function init()
    {
        ob_start();
        if (Constants::$OUTPUT_PERFORMANCE_STATS) {
            $this->tsStart = microtime(true);
        }
        $this->jsonObj = App\HttpResponse::getJsonObject();
        $this->setHeaders();
        $this->startJson();
        $this->startOutputJson();
        $this->process();
        $this->endOutputJson();
        $this->addPerformance();
        $this->endJson();
    }

    public function setHeaders()
    {
        header('Content-Type: application/json;charset=utf-8');
        header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
        header('Pragma: no-cache');        
    }

    public function startJson()
    {
        $this->jsonObj->startAssoc();
    }

    public function startOutputJson()
    {
        $this->jsonObj->startAssoc('Output');        
    }

    public function process()
    {
        switch (true) {
            case Constants::$ROUTE === '/login':
                $login = new App\Login();
                $login->init();
                break;
            case Constants::$ROUTE === '/routes':
                $routes = new App\Routes();
                $routes->init();
                break;
            case Constants::$ROUTE === '/reload':
                if (httpAuthentication()) {
                    $reload = new App\Reload();
                    $reload->init();
                }
                break;
            case strpos(Constants::$ROUTE, '/crons') === 0:
                if (Constants::$REMOTE_ADDR !== getenv('cronRestrictedIp')) {
                    die('Source IP is not supported.');
                }
                $routeArr = explode('/', $this->ROUTE);
                if (
                    isset($routeArr[2]) &&
                    file_exists(Constants::$__DOC_ROOT__ . "/Crons/{$routeArr[2]}.php")
                ) {
                    eval('Crons\\' . $routeArr[2] . '::init($this->ROUTE);');
                } else {
                    die('Invalid request.');
                }
                break;
            default:
                $api = new App\Api();
                $api->init();
                break;
        }        
    }

    public function endOutputJson()
    {
        $this->jsonObj->endAssoc();
        $this->jsonObj->addKeyValue('Status', App\HttpResponse::$httpStatus);
    }

    public function addPerformance()
    {
        if (Constants::$OUTPUT_PERFORMANCE_STATS) {
            $this->tsEnd = microtime(true);
            $time = ($this->tsEnd - $this->tsStart) * 1000;
            $memory = (memory_get_peak_usage()/1000);
        
            $this->jsonObj->startAssoc('Stats');
            $this->jsonObj->startAssoc('Performance');
            $this->jsonObj->encode(
                [
                    'total-time-taken' => ceil($time) . ' ms',
                    'peak-memory-usage' => ceil($memory) . ' KB'
                ]
            );
            $this->jsonObj->endAssoc();
            $this->jsonObj->startAssoc('getrusage');
            $this->jsonObj->encode(getrusage());
            $this->jsonObj->endAssoc();
            $this->jsonObj->endAssoc();
        }
    }

    public function endJson()
    {
        $this->jsonObj->endAssoc();
        $jsonObj = null;
    }
}
$Microservices = new Microservices();
$Microservices->init();
