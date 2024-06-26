<?php
namespace Microservices;

use Microservices\App\Constants;
use Microservices\App\Env;
use Microservices\App\HttpResponse;

/**
 * Microservices Class
 *
 * Class to start Microservices.
 *
 * @category   Microservices
 * @package    Microservices
 * @author     Ramesh Narayan Jangid
 * @copyright  Ramesh Narayan Jangid
 * @version    Release: @1.0.0@
 * @since      Class available since Release 1.0.0
 */
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
    private $jsonEncode = null;

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
        $this->setCors();
        ob_start();
    }

    /**
     * Initialize
     *
     * @return boolean
     */
    public function init()
    {
        Env::init();

        if (Env::$OUTPUT_PERFORMANCE_STATS) {
            $this->tsStart = microtime(true);
        }

        $this->jsonEncode = HttpResponse::getJsonObject();
        
        return HttpResponse::isSuccess();
    }

    /**
     * Process
     *
     * @return boolean
     */
    public function process()
    {
        if (HttpResponse::isSuccess()) $this->startJson();
        if (HttpResponse::isSuccess()) $this->startOutputJson();
        if (HttpResponse::isSuccess()) $this->processApi();
        if (HttpResponse::isSuccess()) $this->endOutputJson();
        if (HttpResponse::isSuccess()) $this->addPerformance();
        if (HttpResponse::isSuccess()) $this->endJson();

        return HttpResponse::isSuccess();
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
        $this->jsonEncode->startObject();
    }

    /**
     * Start Json Output Key
     *
     * @return void
     */
    public function startOutputJson()
    {
        $this->jsonEncode->startObject('Output');      
    }

    /**
     * Process API request
     *
     * @return boolean
     */
    public function processApi()
    {
        $class = null;

        switch (true) {

            case strpos(Constants::$ROUTE, '/cron') === 0:
                if (Constants::$REMOTE_ADDR !== Env::$cronRestrictedIp) {
                    HttpResponse::return4xx(404, 'Source IP is not supported');
                    return;
                }
                $class = __NAMESPACE__ . '\\App\\Cron';
                break;
            
            // Requires HTTP auth username and password
            case Constants::$ROUTE === '/reload':
                $envUsername = 'HttpAuthenticationUser';
                $envPassword = 'HttpAuthenticationPassword';
                if ($this->httpAuthentication($envUsername, $envPassword)) {
                    $class = __NAMESPACE__ . '\\App\\Reload';
                }
                break;
            
            // Generates auth token
            case Constants::$ROUTE === '/login':
                $class = __NAMESPACE__ . '\\App\\Login';
                break;

            // Requires auth token
            case Constants::$ROUTE === '/routes':
                $class = __NAMESPACE__ . '\\App\\Routes';
                break;
            
            // Requires auth token
            case Constants::$ROUTE === '/check':
                $class = __NAMESPACE__ . '\\App\\Check';
                break;
            
            // Requires auth token
            default:
                $class = __NAMESPACE__ . '\\App\\Api';
                break;
        }

        // Class found
        if (!is_null($class)) {
            $api = new $class();
            if ($api->init()) {
                $api->process();
            }
        }

        return HttpResponse::isSuccess();
    }

    /**
     * End Json Output Key
     *
     * @return void
     */
    public function endOutputJson()
    {
        $this->jsonEncode->endObject();
        $this->jsonEncode->addKeyValue('Status', App\HttpResponse::$httpStatus);
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
        
            $this->jsonEncode->startObject('Stats');
            $this->jsonEncode->startObject('Performance');
            $this->jsonEncode->addKeyValue('total-time-taken', "{$time} ms");
            $this->jsonEncode->addKeyValue('peak-memory-usage', "{$memory} KB");
            $this->jsonEncode->endObject();
            $this->jsonEncode->addKeyValue('getrusage', getrusage());
            $this->jsonEncode->endObject();
        }
    }

    /**
     * End Json
     *
     * @return void
     */
    public function endJson()
    {
        $this->jsonEncode->endObject();
        $this->jsonEncode->end();
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
            return;
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
            $this->jsonEncode->streamJson();
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

        $this->jsonEncode = null;
    }

    /**
     * CORS-compliant method
     * 
     * @return void
     */
    private function setCors()
    {
        // Allow from any origin
        if (isset($_SERVER['HTTP_ORIGIN'])) {
            // Decide if the origin in $_SERVER['HTTP_ORIGIN'] is one
            // you want to allow, and if so:
            header("Access-Control-Allow-Origin: {$_SERVER['HTTP_ORIGIN']}");
            header('Access-Control-Allow-Credentials: true');
            header('Access-Control-Max-Age: 86400'); // cache for 1 day
        }
        
        // Access-Control headers are received during OPTIONS requests
        if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
            
            if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_METHOD']))
                // may also be using PUT, PATCH, HEAD etc
                header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
            
            if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']))
                header("Access-Control-Allow-Headers: {$_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']}");
        
            exit(0);
        }
    }

    /**
     * HTTP Authentication
     *
     * @param string $envUsername env variable to match username
     * @param string $envPassword env variable to match password
     * @return boolean
     */
    private function httpAuthentication($envUsername, $envPassword)
    {
        // Check request not from proxy.
        if (
            !isset($_SERVER['REMOTE_ADDR']) ||
            $_SERVER['REMOTE_ADDR'] !== getenv('HttpAuthenticationRestrictedIp')
        ) {
            http_response_code(404);
        }
        if (!isset($_SERVER['PHP_AUTH_USER']) || !isset($_SERVER['PHP_AUTH_PW'])) {
            header('WWW-Authenticate: Basic realm="Test Authentication System"');
            header('HTTP/1.0 401 Unauthorized');
            echo "You must enter a valid login ID and password to access this resource\n";
            return false;
        } else {
            $username = $_SERVER['PHP_AUTH_USER'];
            $password = $_SERVER['PHP_AUTH_PW'];
    
            $validated = ($username === getenv($envUsername)) && ($password === getenv($envPassword));
    
            if (!$validated) {
                header('WWW-Authenticate: Basic realm="My Realm"');
                header('HTTP/1.0 401 Unauthorized');
                die ("Not authorized");
            } else {
                return true;
            }
        }
    }
}
