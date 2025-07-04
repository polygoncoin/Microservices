<?php
namespace Microservices;

use Microservices\App\Constants;
use Microservices\App\Common;
use Microservices\App\Env;
use Microservices\App\HttpStatus;

/**
 * Microservices Class
 *
 * Class to start Services
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
     *
     * @var null|integer
     */
    private $tsStart = null;

    /**
     * End micro timestamp;
     *
     * @var null|integer
     */
    private $tsEnd = null;

    /**
     * Microservices Request Details
     *
     * @var null|array
     */
    public $httpRequestDetails = null;

    /**
     * Microservices Collection of Common Objects
     *
     * @var null|Common
     */
    public $c = null;

    /**
     * Constructor
     *
     * @param array $httpRequestDetails
     * @return void
     */
    public function __construct(&$httpRequestDetails)
    {
        $this->httpRequestDetails = &$httpRequestDetails;

        Constants::init();
        Env::init();
    }

    /**
     * Initialize
     *
     * @return boolean
     * @throws \Exception
     */
    public function init()
    {
        $this->c = new Common($this->httpRequestDetails);
        $this->c->init();

        if (!isset($this->httpRequestDetails['get'][Constants::$ROUTE_URL_PARAM])) {
            throw new \Exception('Missing route', HttpStatus::$NotFound);
        }

        if (Env::$OUTPUT_PERFORMANCE_STATS) {
            $this->tsStart = microtime(true);
        }

        return true;
    }

    /**
     * Process
     *
     * @return boolean
     */
    public function process()
    {
        $this->startJson();
        $this->processApi();
        $this->endOutputJson();
        $this->addPerformance();
        $this->endJson();

        return true;
    }

    /**
     * Start Json
     *
     * @return void
     */
    public function startJson()
    {
        $this->c->httpResponse->dataEncode->startObject();
    }

    /**
     * Process API request
     *
     * @throws \Exception
     * @return boolean
     */
    public function processApi()
    {
        $class = null;

        switch (true) {

            case Env::$allowCronRequest && strpos($this->c->httpRequest->ROUTE, '/'.Env::$cronRequestUriPrefix) === 0:
                if ($this->c->httpRequest->REMOTE_ADDR !== Env::$cronRestrictedIp) {
                    throw new \Exception('Source IP is not supported', HttpStatus::$NotFound);
                }
                $class = __NAMESPACE__ . '\\App\\Cron';
                break;

            // Requires HTTP auth username and password
            case $this->c->httpRequest->ROUTE === '/reload':
                if ($this->c->httpRequest->REMOTE_ADDR !== Env::$cronRestrictedIp) {
                    throw new \Exception('Source IP is not supported', HttpStatus::$NotFound);
                }
                $class = __NAMESPACE__ . '\\App\\Reload';
                break;

            // Generates auth token
            case $this->c->httpRequest->ROUTE === '/login':
                $class = __NAMESPACE__ . '\\App\\Login';
                break;

            // Default - Auth based and Open to web API's
            default:
                $this->c->httpRequest->initGateway();
                $class = __NAMESPACE__ . '\\App\\Api';
                break;
        }

        // Class found
        try {
            if (!is_null($class)) {
                $api = new $class($this->c);
                if ($api->init()) {
                    $api->process();
                }
            }
        } catch (\Exception $e) {
            $this->log($e);
        }

        return true;
    }

    /**
     * End Json Output Key
     *
     * @return void
     */
    public function endOutputJson()
    {
        $this->c->httpResponse->dataEncode->addKeyData('Status', $this->c->httpResponse->httpStatus);
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
            $memory = ceil(memory_get_peak_usage() / 1000);

            $this->c->httpResponse->dataEncode->startObject('Stats');
            $this->c->httpResponse->dataEncode->startObject('Performance');
            $this->c->httpResponse->dataEncode->addKeyData('total-time-taken', "{$time} ms");
            $this->c->httpResponse->dataEncode->addKeyData('peak-memory-usage', "{$memory} KB");
            $this->c->httpResponse->dataEncode->endObject();
            $this->c->httpResponse->dataEncode->addKeyData('getrusage', getrusage());
            $this->c->httpResponse->dataEncode->endObject();
        }
    }

    /**
     * End Json
     *
     * @return void
     */
    public function endJson()
    {
        $this->c->httpResponse->dataEncode->endObject();
        $this->c->httpResponse->dataEncode->end();
    }

    /**
     * Output
     *
     * @return void
     */
    public function outputResults()
    {
        http_response_code($this->c->httpResponse->httpStatus);
        $this->c->httpResponse->dataEncode->streamData();
    }

    /**
     * Headers / CORS
     *
     * @return void
     */
    public function getHeaders()
    {
        $headers = [];
        $headers['Access-Control-Allow-Origin'] = "{$_SERVER['REQUEST_SCHEME']}://{$_SERVER['HTTP_HOST']}";
        $headers['Vary'] = 'Origin';
        $headers['Access-Control-Allow-Headers'] = '*';

        $headers['Referrer-Policy'] = 'origin';
        $headers['X-Frame-Options'] = 'SAMEORIGIN';
        $headers['X-Content-Type-Options'] = 'nosniff';
        $headers['Cross-Origin-Resource-Policy'] = 'same-origin';
        $headers['Cross-Origin-Embedder-Policy'] = 'unsafe-none';
        $headers['Cross-Origin-Opener-Policy'] = 'unsafe-none';

        // Access-Control headers are received during OPTIONS requests
        if ($this->httpRequestDetails['server']['request_method'] == 'OPTIONS') {
            // may also be using PUT, PATCH, HEAD etc
            $headers['Access-Control-Allow-Methods'] = 'GET, POST, PUT, PATCH, DELETE, OPTIONS';
        } else {
            if (Env::$outputDataRepresentation === 'Xml') { // XML headers
                $headers['Content-Type'] = 'text/xml; charset=utf-8';
            } else { // JSON headers
                $headers['Content-Type'] = 'application/json; charset=utf-8';
            }
            $headers['Cache-Control'] = 'no-store, no-cache, must-revalidate, max-age=0';
            $headers['Pragma'] = 'no-cache';
        }

        return $headers;
    }

    /**
     * Log error
     *
     * @param \Exception $e
     * @return void
     * @throws \Exception
     */
    private function log($e)
    {
        throw new \Exception($e->getMessage(), $e->getCode());
    }
}
