<?php
namespace Microservices\App;

use Microservices\App\Constants;
use Microservices\App\CacheKey;
use Microservices\App\DatabaseCacheKey;
use Microservices\App\DbFunctions;
use Microservices\App\Env;
use Microservices\App\HttpStatus;
use Microservices\App\JsonDecode;
use Microservices\App\RateLimiter;
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
     * @var null|boolean
     */
    public $open = null;

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
    private $tokenKey = null;
    private $clientKey = null;
    private $groupKey = null;
    private $cidrKey = null;
    private $cidrChecked = false;

    /**
     * Client Info
     *
     * @var null|array
     */
    public $clientDetails = null;

    /**
     * Group Info
     *
     * @var null|array
     */
    public $groupDetails = null;

    /**
     * User Info
     *
     * @var null|array
     */
    public $userDetails = null;

    /**
     * Rate Limiter
     *
     * @var null|RateLimiter
     */
    private $rateLimiter = null;

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

        $this->HOST = $this->httpRequestDetails['server']['host'];
        $this->REQUEST_METHOD = $this->httpRequestDetails['server']['request_method'];
        $this->REMOTE_ADDR = $this->httpRequestDetails['server']['remote_addr'];
        $this->ROUTE = '/' . trim($this->httpRequestDetails['get'][Constants::$ROUTE_URL_PARAM], '/');

        if (
            isset($this->httpRequestDetails['header'])
            && isset($this->httpRequestDetails['header']['authorization'])
        ) {
            $this->HTTP_AUTHORIZATION = $this->httpRequestDetails['header']['authorization'];
            $this->open = false;
        } elseif ($this->ROUTE === '/login') {
            $this->open = false;
        } else {
            $this->open = true;
        }
    }

    /**
     * Initialize
     *
     * @return void
     */
    public function init()
    {
        return true;
    }

    /**
     * Initialize Gateway
     *
     * @return void
     */
    public function initGateway()
    {
        $this->loadClientDetails();

        if (!$this->open) {
            $this->loadUserDetails();
            $this->checkRemoteIp();
        }
        $this->checkRateLimits();
    }

    /**
     * Load Client Details
     *
     * @return void
     * @throws \Exception
     */
    public function loadClientDetails()
    {
        if (!is_null($this->clientDetails)) return;

        $this->loadCache();

        if ($this->open) {
            $this->clientKey = CacheKey::ClientOpenToWeb($this->HOST);
        } else {
            $this->clientKey = CacheKey::Client($this->HOST);
        }
        if (!$this->cache->cacheExists($this->clientKey)) {
            throw new \Exception("Invalid Host '{$this->HOST}'", HttpStatus::$InternalServerError);
        }

        $this->clientDetails = $this->session['clientDetails'] = json_decode($this->cache->getCache($this->clientKey), true);
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
        if (!is_null($this->userDetails)) return;

        $this->loadCache();

        if (
            !$this->open
            && !is_null($this->HTTP_AUTHORIZATION)
            && preg_match('/Bearer\s(\S+)/', $this->HTTP_AUTHORIZATION, $matches)
        ) {
            $this->session['token'] = $matches[1];
            $this->tokenKey = CacheKey::Token($this->session['token']);
            if (!$this->cache->cacheExists($this->tokenKey)) {
                throw new \Exception('Token expired', HttpStatus::$BadRequest);
            }
            $this->userDetails = $this->session['userDetails'] = json_decode($this->cache->getCache($this->tokenKey), true);
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
        if (!is_null($this->groupDetails)) return;

        $this->loadCache();

        // Load groupDetails
        if (empty($this->session['userDetails']['user_id']) || empty($this->session['userDetails']['group_id'])) {
            throw new \Exception('Invalid session', HttpStatus::$InternalServerError);
        }

        $this->groupKey = CacheKey::Group($this->session['userDetails']['group_id']);
        if (!$this->cache->cacheExists($this->groupKey)) {
            throw new \Exception("Cache '{$this->groupKey}' missing", HttpStatus::$InternalServerError);
        }

        $this->groupDetails = $this->session['groupDetails'] = json_decode($this->cache->getCache($this->groupKey), true);
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
            $this->payloadStream = fopen('php://input', 'rb');
            $this->jsonDecode = new JsonDecode($this->payloadStream);
            $this->jsonDecode->init();

            rewind($this->payloadStream);
            $this->jsonDecode->indexJSON();
            $this->session['payloadType'] = $this->jsonDecode->jsonType();
        }
    }

    /**
     * Validate request IP
     *
     * @return void
     * @throws \Exception
     */
    public function checkRemoteIp()
    {
        $groupId = $this->userDetails['group_id'];

        $this->cidrKey = CacheKey::CIDR($this->userDetails['group_id']);
        if ($this->cache->cacheExists($this->cidrKey)) {
            $this->cidrChecked = true;
            $cidrs = json_decode($this->cache->getCache($this->cidrKey), true);
            $ipNumber = ip2long($this->REMOTE_ADDR);
            $isValidIp = false;
            foreach ($cidrs as $cidr) {
                if ($cidr['start'] <= $ipNumber && $ipNumber <= $cidr['end']) {
                    $isValidIp = true;
                    break;
                }
            }
            if (!$isValidIp) {
                throw new \Exception('IP not supported', HttpStatus::$BadRequest);
            }
        }
    }

    /**
     * Check Rate Limits
     *
     * @return void
     */
    private function checkRateLimits()
    {
        $this->rateLimiter = new RateLimiter();

        $rateLimitChecked = false;

        // Client Rate Limiting
        if (
            !empty($this->clientDetails['rateLimiterMaxRequests'])
            && !empty($this->clientDetails['rateLimiterSecondsWindow'])
        ) {
            $rateLimitChecked = $this-checkRateLimit(
                $RateLimiterGroupPrefix = getenv('RateLimiterClientPrefix'),
                $RateLimiterMaxRequests = $this->clientDetails['rateLimiterMaxRequests'],
                $RateLimiterSecondsWindow = $this->clientDetails['rateLimiterSecondsWindow'],
                $key = $this->clientDetails['client_id']
            );
        }

        if (!$this->open) {
            // Group Rate Limiting
            if (
                !empty($this->groupDetails['rateLimiterMaxRequests'])
                && !empty($this->groupDetails['rateLimiterSecondsWindow'])
            ) {
                $rateLimitChecked = $this-checkRateLimit(
                    $RateLimiterGroupPrefix = getenv('RateLimiterGroupPrefix'),
                    $RateLimiterMaxRequests = $this->groupDetails['rateLimiterMaxRequests'],
                    $RateLimiterSecondsWindow = $this->groupDetails['rateLimiterSecondsWindow'],
                    $key = $this->clientDetails['client_id'] . ':' . $this->userDetails['group_id']
                );
            }

            // User Rate Limiting
            if (
                !empty($this->userDetails['rateLimiterMaxRequests'])
                && !empty($this->userDetails['rateLimiterSecondsWindow'])
            ) {
                $rateLimitChecked = $this->checkRateLimit(
                    $RateLimiterUserPrefix = getenv('RateLimiterUserPrefix'),
                    $RateLimiterMaxRequests = $this->groupDetails['rateLimiterMaxRequests'],
                    $RateLimiterSecondsWindow = $this->groupDetails['rateLimiterSecondsWindow'],
                    $key = $this->clientDetails['client_id'] . ':' . $this->userDetails['group_id'] . ':' . $this->userDetails['user_id']
                );
            }
        }

        // Rate limit open traffic (not limited by allowed IPs/CIDR and allowed Rate Limits to users)
        if ($this->cidrChecked === false && $rateLimitChecked === false) {
            $this->checkRateLimit(
                $RateLimiterIPPrefix = getenv('RateLimiterIPPrefix'),
                $RateLimiterIPMaxRequests = getenv('RateLimiterIPMaxRequests'),
                $RateLimiterIPSecondsWindow = getenv('RateLimiterIPSecondsWindow'),
                $key = $this->REMOTE_ADDR
            );
        }
    }

    /**
     * Check Rate Limit
     *
     * @param string $RateLimiterPrefix
     * @param int    $RateLimiterMaxRequests
     * @param int    $RateLimiterSecondsWindow
     * @param string $key
     * @return void
     * @throws \Exception
     */
    private function checkRateLimit(
        $RateLimiterPrefix,
        $RateLimiterMaxRequests,
        $RateLimiterSecondsWindow,
        $key
    ) {
        try {
            $result = $this->rateLimiter->check(
                $RateLimiterPrefix,
                $RateLimiterMaxRequests,
                $RateLimiterSecondsWindow,
                $key
            );

            if ($result['allowed']) {
                // Process the request
                return true;
            } else {
                // Return 429 Too Many Requests
                throw new \Exception($result['resetAt'] - time(), HttpStatus::$TooManyRequests);
            }

        } catch (\Exception $e) {
            // Handle connection errors
            throw new \Exception($e->getMessage(), $e->getCode());
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

    private function loadCache()
    {
        if (!is_null($this->cache)) {
            return;
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
}
