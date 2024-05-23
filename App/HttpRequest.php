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
     * Allow config request (global flag from env): 1 = true / 0 = false
     *
     * @var bool
     */
    public static $allowConfigRequest = 0;

    /**
     * Is a config request
     *
     * @var bool
     */
    public static $isConfigRequest = false;

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
     * Cahe Object
     *
     * @var string
     */
    public static $cache = null;

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
     * Inputs detials of a request
     *
     * @var array
     */
    public static $input = null;

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
        self::$REQUEST_METHOD = REQUEST_METHOD;
        self::$HTTP_AUTHORIZATION = HTTP_AUTHORIZATION;
        self::$REMOTE_ADDR = REMOTE_ADDR;

        self::$allowConfigRequest = getenv('allowConfigRequest');
        
        Cache::$cacheType = 'cacheType';
        Cache::$hostname = 'cacheHostname';
        Cache::$port = 'cachePort';
        Cache::$username = 'cacheUsername';
        Cache::$password = 'cachePassword';
        Cache::$database = 'cacheDatabase';

        self::$cache = Cache::getObject();
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
            if (!self::$cache->cacheExists($token)) {
                HttpResponse::return5xx(501, 'Cache token missing');
            }
            self::$input['readOnlySession'] = json_decode(self::$cache->getCache($token), true);
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
    public static function initSession()
    {
        if (empty(self::$input['readOnlySession']['user_id']) || empty(self::$input['readOnlySession']['group_id'])) {
            HttpResponse::return4xx(404, 'Invalid session');
        }
        self::$userId = self::$input['readOnlySession']['user_id'];
        self::$groupId = self::$input['readOnlySession']['group_id'];
        $key = 'group:'.self::$groupId;
        if (!self::$cache->cacheExists($key)) {
            HttpResponse::return5xx(501, "Cache '{$key}' missing.");
        }
        $groupInfoArr = json_decode(self::$cache->getCache($key), true);
        Database::$dbType = $groupInfoArr['db_server_type'];
        Database::$hostname = $groupInfoArr['db_hostname'];
        Database::$port = $groupInfoArr['db_port'];
        Database::$username = $groupInfoArr['db_username'];
        Database::$password = $groupInfoArr['db_password'];
        Database::$database = $groupInfoArr['db_database'];
    }

    /**
     * Validate request IP
     *
     * @return void
     */
    public static function checkRemoteIp()
    {
        $groupId = self::$input['readOnlySession']['group_id'];
        $key = 'cidr:'.self::$groupId;
        if (self::$cache->cacheExists($key)) {
            $cidrs = json_decode(self::$cache->getCache($key), true);
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
        $routeLastElementPos = count(self::$routeElements) - 1;
        self::$isConfigRequest = (self::$routeElements[$routeLastElementPos]) === 'config';
        $configuredUri = [];
        foreach(self::$routeElements as $key => $e) {
            $pos = false;
            if (isset($routes[$e])) {
                if (
                    self::$allowConfigRequest == 1 &&
                    self::$isConfigRequest && 
                    $key === $routeLastElementPos &&
                    $routes[$e] === true
                ) {
                    break;
                }
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
                        self::$input['uriParams'][$paramName] = urldecode($e);
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
            HttpResponse::return5xx(501, 'Missing route configuration file for ' . self::$REQUEST_METHOD . ' method');
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
            self::urlDecode($_GET);
            self::$input['payloadArr'] = !empty($_GET) ? $_GET : [];
        } else {
            // Load Payload
            parse_str(file_get_contents('php://input'), $payloadArr);
            if (!isset($payloadArr['Payload'])) {
                HttpResponse::return4xx(404, 'Invalid "Payload"');
            }
            $payloadArr = json_decode($payloadArr['Payload'], true);
            if (is_null($payloadArr)) {
                HttpResponse::return4xx(404, 'Invalid Payload JSON');
            }
            self::$input['payloadArr'] = $payloadArr;
        }
        self::$input['payloadArrType'] = self::payloadType();
        if (self::$input['payloadArrType'] === 'Object') {
            self::$input['payloadArr'] = [self::$input['payloadArr']];
        }
    }

    /**
     * Function to find payload is an object/array
     *
     * @param array $arr Array vales to be decoded. Basically $_GET.
     * @return void
     */
    public static function urlDecode(&$arr)
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

    /**
     * Function to find payload is an object/array
     *
     * @return boolean
     */
    public static function payloadType()
    {
        $payloadType = 'Array';
        $i = 0;
        foreach (self::$input['payloadArr'] as $k => &$v) {
            if ($k !== $i++) {
                $payloadType = 'Object';
                break;
            }
        }
        return $payloadType;
    }
}
