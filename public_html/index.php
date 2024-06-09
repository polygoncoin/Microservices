<?php
require_once __DIR__ . '/../autoload.php';

use App\Constants;
use App\Env;
use App\HttpResponse;

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

    /**
     * JsonEncode class object
     *
     * @var object
     */
    private $jsonObj = null;


    /**
     * Output Buffer
     *
     * @var string
     */
    private $outputBuffer = null;

    /**
     * Constructor
     */
    public function __construct()
    {
        ob_start();
    }

    /**
     * Initialize
     *
     * @return void
     */
    public function init()
    {
        Constants::init();
        Env::init();

        if (Env::$OUTPUT_PERFORMANCE_STATS) {
            $this->tsStart = microtime(true);
        }
        $this->jsonObj = HttpResponse::getJsonObject();

        return true;
    }

    /**
     * Process
     *
     * @return void
     */
    public function process()
    {
        if (is_null(HttpResponse::$httpResponse)) $this->startJson();
        if (is_null(HttpResponse::$httpResponse)) $this->startOutputJson();
        if (is_null(HttpResponse::$httpResponse)) $this->processApi();
        if (is_null(HttpResponse::$httpResponse)) $this->endOutputJson();
        if (is_null(HttpResponse::$httpResponse)) $this->addPerformance();
        if (is_null(HttpResponse::$httpResponse)) $this->endJson();

        return true;
    }

    /**
     * Set Headers
     *
     * @return void
     */
    public function setHeaders()
    {
        header('Content-Type: application/json;charset=utf-8');
        header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
        header('Pragma: no-cache');        
    }

    /**
     * Start Json
     *
     * @return void
     */
    public function startJson()
    {
        $this->jsonObj->startAssoc();
    }

    /**
     * Start Json Output Key
     *
     * @return void
     */
    public function startOutputJson()
    {
        $this->jsonObj->startAssoc('Output');        
    }

    /**
     * Process API request
     *
     * @return void
     */
    public function processApi()
    {
        $class = null;
        switch (true) {
            case Constants::$ROUTE === '/login':
                $class = 'App\\Login';
                break;
            case Constants::$ROUTE === '/routes':
                $class = 'App\\Routes';
                break;
            case Constants::$ROUTE === '/check':
                $class = 'App\\Check';
                break;
            case Constants::$ROUTE === '/reload':
                if (httpAuthentication()) {
                    $class = 'App\\Reload';
                }
                break;
            case strpos(Constants::$ROUTE, '/crons') === 0:
                if (Constants::$REMOTE_ADDR !== Env::$cronRestrictedIp) {
                    HttpResponse::return4xx(404, 'Source IP is not supported');
                    return;
                }
                $routeArr = explode('/', $this->ROUTE);
                $cron = ucfirst($routeArr[2]);
                if (
                    isset($routeArr[2]) &&
                    file_exists(Constants::$DOC_ROOT . "/Crons/{$cron}.php")
                ) {
                    $class = "Crons\\{$cron}";
                } else {
                    HttpResponse::return4xx(404, 'Invalid request');
                    return;
                }
                break;
            default:
                $class = 'App\\Api';
                break;
        }
        if (!is_null($class)) {
            $api = new $class();
            if ($api->init()) {
                return $api->process();
            } else {
                return false;
            }
        }
        return false;
    }

    /**
     * End Json Output Key
     *
     * @return void
     */
    public function endOutputJson()
    {
        $this->jsonObj->endAssoc();
        $this->jsonObj->addKeyValue('Status', App\HttpResponse::$httpStatus);
    }

    /**
     * Add Performance details
     *
     * @return void
     */
    public function addPerformance()
    {
        if (Env::$OUTPUT_PERFORMANCE_STATS) {
            $this->tsEnd = microtime(true);
            $time = ceil(($this->tsEnd - $this->tsStart) * 1000);
            $memory = ceil(memory_get_peak_usage()/1000);
        
            $this->jsonObj->startAssoc('Stats');
            $this->jsonObj->startAssoc('Performance');
            $this->jsonObj->addKeyValue('total-time-taken', "{$time} ms");
            $this->jsonObj->addKeyValue('peak-memory-usage', "{$memory} KB");
            $this->jsonObj->endAssoc();
            $this->jsonObj->addKeyValue('getrusage', getrusage());
            $this->jsonObj->endAssoc();
        }
    }

    /**
     * End Json
     *
     * @return void
     */
    public function endJson()
    {
        $this->jsonObj->endAssoc();
        $this->jsonObj->end();
    }

    /**
     * End Json
     *
     * @return void
     */
    public function checkOutputBuffer()
    {
        $outputBuffer = ob_get_clean();
        if (!empty($outputBuffer) && Env::$ENVIRONMENT === Constants::$PRODUCTION) {
            $this->outputBuffer = $outputBuffer;
            $log = [
                'datetime' => date('Y-m-d H:i:s'),
                'input' => HttpRequest::$input,
                'error' => $str
            ];
            Logs::log('error', json_encode($log));
            HttpResponse::return5xx(501, 'Error: Facing server side error with API.');
        }
    }

    /**
     * End Json
     *
     * @return void
     */
    public function streamJson()
    {
        $this->setHeaders();
        if (!is_null(HttpResponse::$httpResponse)) {
            echo HttpResponse::$httpResponse;
        } else {
            $this->jsonObj->streamJson();
        }    
    }

    /**
     * Output
     *
     * @return void
     */
    public function outputResults()
    {
        if (!is_null($this->outputBuffer)) {
            echo $this->outputBuffer;
        } else {
            $this->streamJson();
        }
        $this->jsonObj = null;
    }
}

$Microservices = new Microservices();
if ($Microservices->init()) {
    $Microservices->process();
}
$Microservices->outputResults();
