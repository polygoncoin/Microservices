<?php
namespace App;

use App\Constants;
use App\HttpResponse;
use App\Logs;
use App\Servers\Cache\Cache;
use App\Servers\Database\Database;

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
class HttpRequest
{
    /**
     * Raw route / Configured Uri
     *
     * @var string
     */
    public static $configuredUri = '';

    /**
     * Array containing details of received route elements
     *
     * @var array
     */
    public static $routeElements = [];

    /**
     * Locaton of File containing code for route
     *
     * @var string
     */
    public static $__file__ = null;

    /**
     * HTTP request method
     *
     * @var string
     */
    public static $REQUEST_METHOD = null;

    /**
     * HTTP_AUTHORIZATION header
     *
     * @var string
     */
    public static $HTTP_AUTHORIZATION = null;

    /**
     * Remote IP
     *
     * @var string
     */
    public static $REMOTE_ADDR = null;

    /**
     * Inputs detials of a request
     *
     * @var array
     */
    public static $input = null;

    /**
     * Client database server type
     *
     * @var string
     */
    public static $clientServerType = null;

    /**
     * Client database hostname
     *
     * @var string
     */
    public static $clientHostname = null;

    /**
     * Client database username
     *
     * @var string
     */
    public static $clientUsername = null;

    /**
     * Client database password
     *
     * @var string
     */
    public static $clientPassword = null;

    /**
     * Client database
     *
     * @var string
     */
    public static $clientDatabase = null;

    /**
     * Logged-in User ID
     *
     * @var int
     */
    public static $userId = null;

    /**
     * Logged-in user Group ID
     *
     * @var int
     */
    public static $groupId = null;

    /**
     * Initialization
     *
     * @return void
     */
    public static function init()
    {
        self::$REQUEST_METHOD = $_SERVER['REQUEST_METHOD'];
        self::$HTTP_AUTHORIZATION = $_SERVER['HTTP_AUTHORIZATION'];
        self::$REMOTE_ADDR = $_SERVER['REMOTE_ADDR'];
        
        Cache::connect(
            'Redis',
            'cacheHostname',
            'cachePort',
            'cachePassword',
            'cacheDatabase'
        );

        self::loadToken();
        self::initSession();
        self::parseRoute();
    }

    /**
     * Loads token from HTTP_AUTHORIZATION header
     *
     * @return void
     */
    public static function loadToken()
    {
        if (preg_match('/Bearer\s(\S+)/', self::$HTTP_AUTHORIZATION, $matches)) {
            self::$input['token'] = $matches[1];
            $token = self::$input['token'];
            if (!Cache::$cache->cacheExists($token)) {
                HttpResponse::return5xx(501, "Cache token missing.");
            }
            self::$input['readOnlySession'] = json_decode(Cache::$cache->getCache($token), true);
            self::checkRemoteIp();
        } else {
            HttpResponse::return4xx(404, 'Missing token in authorization header');   
        }
        if (empty(self::$input['token'])) {
            HttpResponse::return4xx(404, 'Missing token');
        }
    }

    /**
     * Load session with help of token
     *
     * @return void
     */
    private static function initSession()
    {
        if (empty(self::$input['readOnlySession']['user_id']) || empty(self::$input['readOnlySession']['group_id'])) {
            HttpResponse::return4xx(404, 'Invalid session');
        }
        self::$userId = self::$input['readOnlySession']['user_id'];
        self::$groupId = self::$input['readOnlySession']['group_id'];
        $key = "group:".self::$groupId;
        if (!Cache::$cache->cacheExists($key)) {
            HttpResponse::return5xx(501, "Cache '{$key}' missing.");
        }
        $groupInfoArr = json_decode(Cache::$cache->getCache($key), true);
        self::$clientServerType = $groupInfoArr['db_server_type'];
        self::$clientHostname = $groupInfoArr['db_hostname'];
        self::$clientUsername = $groupInfoArr['db_username'];
        self::$clientPassword = $groupInfoArr['db_password'];
        self::$clientDatabase = $groupInfoArr['db_database'];
        Database::connect(
            self::$clientServerType,
            self::$clientHostname,
            self::$clientUsername,
            self::$clientPassword,
            self::$clientDatabase
        );
    }

    /**
     * Validate request IP
     *
     * @return void
     */
    private static function checkRemoteIp()
    {
        $groupId = self::$input['readOnlySession']['group_id'];
        $key = "group:".self::$groupId.":cidr";
        if (Cache::$cache->cacheExists($key)) {
            $cidrs = json_decode(Cache::$cache->getCache($key), true);
            $isValidIp = false;
            foreach ($cidrs as $cidr) {
                if (cidr_match(self::$REMOTE_ADDR, $cidr)) {
                    $isValidIp = true;
                    break;
                }
            }
            if (!$isValidIp) {
                HttpResponse::return4xx(404, 'Invalid request.');
            }
        }
    }

    /**
     * Parse route as per method
     *
     * @return void
     */
    public static function parseRoute()
    {
        $routeFileLocation = __DOC_ROOT__ . '/Config/Routes/' . self::$input['readOnlySession']['group_name'] . '/' . self::$REQUEST_METHOD . 'routes.php';
        if (file_exists($routeFileLocation)) {
            $routes = require $routeFileLocation;
        } else {
            HttpResponse::return5xx(501, 'Missing route file for ' . self::$REQUEST_METHOD . ' method');
        }
        self::$routeElements = explode('/', trim(ROUTE, '/'));
        $configuredUri = [];
        foreach(self::$routeElements as $key => $e) {
            $pos = false;
            if (isset($routes[$e])) {
                $configuredUri[] = $e;
                $routes = &$routes[$e];
                continue;
            } else {
                if (is_array($routes)) {
                    $foundIntRoute = false;
                    $foundStringRoute = false;
                    foreach (array_keys($routes) as $r) {
                        // Is a dynamic URI element
                        if (strpos($r, '{') === 0) {
                            // Check for compulsary values
                            $dynamicRoute = trim($r, '{}');
                            $preferredValues = [];
                            if (strpos($r, '|') !== false) {
                                list($dynamicRoute, $preferredValuesString) = explode('|', $dynamicRoute);
                                $preferredValues = ((strlen($preferredValuesString) > 0) ? explode(',', $preferredValuesString) : []);
                            }
                            list($paramName, $paramDataType) = explode(':', $dynamicRoute);
                            if (!in_array($paramDataType, ['int','string'])) {
                                HttpResponse::return5xx(501, 'Invalid datatype set for Route');
                            }
                            if (count($preferredValues) > 0 && !in_array($e, $preferredValues)) {
                                HttpResponse::return4xx(404, $r);
                            }
                            if ($paramDataType === 'int') {
                                if (!ctype_digit($e)) {
                                    HttpResponse::return4xx(404, "Invalid {$paramName}");
                                } else {
                                    $foundIntRoute = $r;
                                }
                            } else {
                                $foundStringRoute = $r;
                            }
                        }
                    }
                    if ($foundIntRoute) {
                        $configuredUri[] = $foundIntRoute;
                        self::$input['uriParams'][$paramName] = (int)$e;
                    } else if ($foundStringRoute) {
                        $configuredUri[] = $foundStringRoute;
                        self::$input['uriParams'][$paramName] = $e;
                    } else {
                        HttpResponse::return4xx(404, 'Route not supported');
                    }
                } else {
                    HttpResponse::return4xx(404, 'Route not supported');
                }
                $routes = &$routes[(($foundIntRoute) ? $foundIntRoute : $foundStringRoute)];
            }
        }
        self::$configuredUri = '/' . implode('/', $configuredUri);

        // Set route code file.
        if (isset($routes['__file__']) && file_exists($routes['__file__'])) {
            self::$__file__ = $routes['__file__'];
            if (empty(self::$__file__) || !file_exists(self::$__file__)) {
                HttpResponse::return5xx(501, 'Path cannot be empty');
            }
        } elseif ($routes['__file__'] != '') {
            HttpResponse::return5xx(501, 'Missing route configuration file for' . " {$REQUEST_METHOD} " . 'method');
        }
    }

    /**
     * Loads request payoad
     *
     * @return void
     */
    public static function loadPayload()
    {
        if (self::$REQUEST_METHOD === Constants::READ) {
            self::$input['payloadArr'] = $_GET;
        } else {
            // Load Payload
            parse_str(file_get_contents('php://input'), $payloadArr);
            if (!isset($payloadArr['data'])) {
                HttpResponse::return4xx(404, 'Invalid data payload');
            }
            $payloadArr = json_decode($payloadArr['data'], true);
            if (is_null($payloadArr)) {
                HttpResponse::return4xx(404, 'Invalid payload JSON');
            }
            self::$input['payloadArr'] = $payloadArr;
        }
        self::$input['payloadArrType'] = self::payloadType(self::$input['payloadArr']);
        if (self::$input['payloadArrType'] === 'Object') {
            self::$input['payloadArr'] = [self::$input['payloadArr']];
        }
    }

    /**
     * Function to find payload is an object/array
     *
     * @param array $payload
     * @return boolean
     */
    public static function payloadType($payload)
    {
        $payloadType = 'Array';
        $i = 0;
        foreach ($payload as $k => &$v) {
            if ($k !== $i++) {
                $payloadType = 'Object';
                break;
            }
        }
        return $payloadType;
    }
}
