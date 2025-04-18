<?php
namespace Microservices\App;

use Microservices\App\Constants;
use Microservices\App\CacheKey;
use Microservices\App\DatabaseCacheKey;
use Microservices\App\Env;
use Microservices\App\HttpStatus;
use Microservices\App\JsonDecode;
use Microservices\App\RouteParser;
use Microservices\App\Servers\Cache\AbstractCache;
use Microservices\App\Servers\Database\AbstractDatabase;

/*
 * Class handling details of HTTP request
 *
 * This class is built to process and handle HTTP request
 *
 * @category   HTTP Request
 * @package    Microservices
 * @author     Ramesh Narayan Jangid
 * @copyright  Ramesh Narayan Jangid
 * @version    Release: @1.0.0@
 * @since      Class available since Release 1.0.0
 */
class HttpRequest extends RouteParser
{
    /**
     * Raw route / Configured Uri
     *
     * @var string
     */
    public $configuredUri = '';

    /**
     * Array containing details of received route elements
     *
     * @var string[]
     */
    public $routeElements = [];

    /**
     * Is a config request flag
     *
     * @var boolean
     */
    public $isConfigRequest = false;

    /**
     * Locaton of File containing code for route
     *
     * @var string
     */
    public $__file__ = null;

    /**
     * Session detials of a request
     *
     * @var null|array
     */
    public $session = null;

    /** @var null|integer */
    public $clientId = null;

    /** @var null|integer */
    public $groupId = null;

    /** @var null|integer */
    public $userId = null;

    /**
     * Caching Object
     *
     * @var null|AbstractCache
     */
    public $cache = null;

    /**
     * Sql Data caching Object
     *
     * @var null|AbstractCache
     */
    public $sqlCache = null;

    /**
     * Database Object
     *
     * @var null|AbstractDatabase
     */
    public $db = null;

    /**
     * Json Decode Object
     *
     * @var null|JsonDecode
     */
    public $jsonDecode = null;

    /**
     * Microservices Request Details
     *
     * @var null|array
     */
    public $httpRequestDetails = null;

    /**
     * Open To World Request
     *
     * @var boolean
     */
    public $open = false;

    /**
     * Details var from $httpRequestDetails
     */
    public $HOST = null;
    public $REQUEST_METHOD = null;
    public $HTTP_AUTHORIZATION = null;
    public $REMOTE_ADDR = null;
    public $ROUTE = null;

    /**
     * Cache Keys
     */
    public $tokenKey = null;
    public $clientKey = null;
    public $groupKey = null;
    public $cidr_key = null;

    /**
     * Payload stream
     */
    private $payloadStream = null;

    /**
     * Constructor
     *
     * @param array $httpRequestDetails
     */
    public function __construct(&$httpRequestDetails)
    {
        $this->httpRequestDetails = &$httpRequestDetails;
    }

    /**
     * Initialize
     *
     * @return boolean
     */
    public function init()
    {
        $this->HOST = $this->httpRequestDetails['server']['host'];
        $this->REQUEST_METHOD = $this->httpRequestDetails['server']['request_method'];
        if (isset($this->httpRequestDetails['header']['authorization'])) {
            $this->HTTP_AUTHORIZATION = $this->httpRequestDetails['header']['authorization'];
        } else {
            $this->open = true;
        }
        $this->REMOTE_ADDR = $this->httpRequestDetails['server']['remote_addr'];
        $this->ROUTE = '/' . trim($this->httpRequestDetails['get'][Constants::$ROUTE_URL_PARAM], '/');

        if ($this->REQUEST_METHOD !== 'GET') {
            $this->payloadStream = fopen('php://input', 'rb');
            $this->jsonDecode = new JsonDecode($this->payloadStream);
            $this->jsonDecode->init();    
        }

        $this->cache = $this->connectCache(
            getenv('cacheType'),
            getenv('cacheHostname'),
            getenv('cachePort'),
            getenv('cacheUsername'),
            getenv('cachePassword'),
            getenv('cacheDatabase')
        );
    }

    /**
     * Load Client Details
     *
     * @return void
     * @throws \Exception
     */
    public function loadClientDetails()
    {
        $this->clientKey = CacheKey::Client($this->HOST);
        if (!$this->cache->cacheExists($this->clientKey)) {
            throw new \Exception("Invalid Host '{$this->HOST}'", HttpStatus::$InternalServerError);
        }

        $this->session['clientDetails'] = json_decode($this->cache->getCache($this->clientKey), true);
        $this->clientId = $this->session['clientDetails']['client_id'];
    }

    /**
     * Load User Details
     *
     * @return void
     * @throws \Exception
     */
    public function loadUserDetails()
    {
        if (preg_match('/Bearer\s(\S+)/', $this->HTTP_AUTHORIZATION, $matches)) {
            $this->session['token'] = $matches[1];
            $this->tokenKey = CacheKey::Token($this->session['token']);
            if (!$this->cache->cacheExists($this->tokenKey)) {
                throw new \Exception('Token expired', HttpStatus::$BadRequest);
            }
            $this->session['userDetails'] = json_decode($this->cache->getCache($this->tokenKey), true);
            $this->groupId = $this->session['userDetails']['group_id'];
            $this->userId = $this->session['userDetails']['user_id'];
        }
        if (empty($this->session['token'])) {
            throw new \Exception('Token missing', HttpStatus::$BadRequest);
        }
    }

    /**
     * Load User Details
     *
     * @return void
     * @throws \Exception
     */
    public function loadGroupDetails()
    {
        // Load groupDetails
        if (empty($this->session['userDetails']['user_id']) || empty($this->session['userDetails']['group_id'])) {
            throw new \Exception('Invalid session', HttpStatus::$InternalServerError);
        }

        $this->groupKey = CacheKey::Group($this->session['userDetails']['group_id']);
        if (!$this->cache->cacheExists($this->groupKey)) {
            throw new \Exception("Cache '{$this->groupKey}' missing", HttpStatus::$InternalServerError);
        }

        $this->session['groupDetails'] = json_decode($this->cache->getCache($this->groupKey), true);
    }

    /**
     * Loads request payoad
     *
     * @return void
     */
    public function loadPayload()
    {
        if ($this->REQUEST_METHOD === Constants::$GET) {
            $this->urlDecode($_GET);
            $this->session['payloadType'] = 'Object';
            $this->session['payload'] = !empty($_GET) ? $_GET : [];
        } else {
            // Load Payload
            rewind($this->payloadStream);
            $this->jsonDecode->indexJSON();
            $this->session['payloadType'] = $this->jsonDecode->jsonType();
        }
    }

    /**
     * Function to find payload is an object/array
     *
     * @param array $arr Array vales to be decoded. Basically $_GET
     * @return void
     */
    public function urlDecode(&$arr)
    {
        if (is_array($arr)) {
            foreach ($arr as $key => &$value) {
                if (is_array($value)) {
                    $this->urlDecode($value);
                } else {
                    $decodedVal = urldecode($value);
                    $array = json_decode($decodedVal, true);
                    if (!is_null($array)) {
                        $value = $array;
                    } else {
                        $value = $decodedVal;
                    }
                }
            }
        } else {
            $decodedVal = urldecode($arr);
            $array = json_decode($decodedVal, true);
            if (!is_null($array)) {
                $arr = $array;
            } else {
                $arr = $decodedVal;
            }
        }
    }
}
