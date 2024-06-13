<?php
namespace App;

use App\Constants;
use App\Env;
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
     * Initialize
     *
     * @return void
     */
    public static function init()
    {
        Env::$cacheType = getenv('cacheType');
        Env::$cacheHostname = getenv('cacheHostname');
        Env::$cachePort = getenv('cachePort');
        Env::$cacheUsername = getenv('cacheUsername');
        Env::$cachePassword = getenv('cachePassword');
        Env::$cacheDatabase = getenv('cacheDatabase');
    
        self::$cache = Cache::getObject();
    }

    /**
     * Loads token from HTTP_AUTHORIZATION
     *
     * @return void
     */
    public static function loadToken()
    {
        if (!is_null(Constants::$HTTP_AUTHORIZATION) && preg_match('/Bearer\s(\S+)/', Constants::$HTTP_AUTHORIZATION, $matches)) {
            self::$input['token'] = $matches[1];
            $token = self::$input['token'];
            if (!self::$cache->cacheExists($token)) {
                HttpResponse::return5xx(501, 'Cache token missing');
                return;
            }
            self::$input['readOnlySession'] = json_decode(self::$cache->getCache($token), true);
            self::$userId = self::$input['readOnlySession']['user_id'];
            self::$groupId = self::$input['readOnlySession']['group_id'];    
            self::checkRemoteIp();
        } else {
            HttpResponse::return4xx(404, 'Missing token in authorization header');
            return;
        }
        if (empty(self::$input['token'])) {
            HttpResponse::return4xx(404, 'Missing token');
            return;
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
            return;
        }
        $key = 'group:'.self::$groupId;
        if (!self::$cache->cacheExists($key)) {
            HttpResponse::return5xx(501, "Cache '{$key}' missing.");
            return;
        }
        $groupInfoArr = json_decode(self::$cache->getCache($key), true);

        Env::$dbType = getenv($groupInfoArr['db_server_type']);
        Env::$dbHostname = getenv($groupInfoArr['db_hostname']);
        Env::$dbPort = getenv($groupInfoArr['db_port']);
        Env::$dbUsername = getenv($groupInfoArr['db_username']);
        Env::$dbPassword = getenv($groupInfoArr['db_password']);
        Env::$dbDatabase = getenv($groupInfoArr['db_database']);
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
            $ipNumber = ip2long(Constants::$REMOTE_ADDR);
            $isValidIp = false;
            foreach ($cidrs as $cidr) {
                if ($cidr['start'] <= $ipNumber && $ipNumber <= $cidr['end']) {
                    $isValidIp = true;
                    break;
                }
            }
            if (!$isValidIp) {
                HttpResponse::return4xx(404, 'IP not supported');
                return;
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
        $routeFileLocation = Constants::$DOC_ROOT . '/Config/Routes/' . self::$input['readOnlySession']['group_name'] . '/' . Constants::$REQUEST_METHOD . 'routes.php';
        if (file_exists($routeFileLocation)) {
            $routes = require $routeFileLocation;
        } else {
            HttpResponse::return5xx(501, 'Missing route file for ' . Constants::$REQUEST_METHOD . ' method');
            return;
        }
        self::$routeElements = explode('/', trim(Constants::$ROUTE, '/'));
        $routeLastElementPos = count(self::$routeElements) - 1;
        Env::$isConfigRequest = (self::$routeElements[$routeLastElementPos]) === 'config';
        $configuredUri = [];
        foreach(self::$routeElements as $key => $element) {
            $pos = false;
            if (isset($routes[$element])) {
                if (
                    Env::$allowConfigRequest == 1 &&
                    Env::$isConfigRequest && 
                    $routes[$element] === true
                ) {
                    break;
                }
                $configuredUri[] = $element;
                $routes = &$routes[$element];
                if (strpos($element, '{') === 0) {
                    $param = substr($element, 1, strpos($element, ':') - 1);
                    self::$input['uriParams'][$param] = $element;
                }
                continue;
            } else {
                if (is_array($routes)) {
                    $foundIntRoute = false;
                    $foundStringRoute = false;
                    foreach (array_keys($routes) as $routeElement) {
                        if (strpos($routeElement, '{') === 0) {// Is a dynamic URI element
                            $paramName = self::processRouteElement($routeElement, $element, $foundIntRoute, $foundStringRoute);
                        }
                    }
                    if ($foundIntRoute) {
                        $configuredUri[] = $foundIntRoute;
                        self::$input['uriParams'][$paramName] = (int)$element;
                    } else if ($foundStringRoute) {
                        $configuredUri[] = $foundStringRoute;
                        self::$input['uriParams'][$paramName] = urldecode($element);
                    } else {
                        HttpResponse::return4xx(404, 'Route not supported');
                        return;
                    }
                } else {
                    HttpResponse::return4xx(404, 'Route not supported');
                    return;
                }
                $routes = &$routes[(($foundIntRoute) ? $foundIntRoute : $foundStringRoute)];
            }
        }
        self::$configuredUri = '/' . implode('/', $configuredUri);

        self::validateConfigFile($routes);
    }

    /**
     * Process Route Element
     *
     * @param string $routeElement     Configured route element
     * @param string $element          Element
     * @param string $foundIntRoute    Found as Integer route element
     * @param string $foundStringRoute Found as String route element
     * @return string
     */
    private static function processRouteElement($routeElement, &$element, &$foundIntRoute, &$foundStringRoute)
    {
        // Is a dynamic URI element
        if (strpos($routeElement, '{') !== 0) {
            return false;
        }
        // Check for compulsary values
        $dynamicRoute = trim($routeElement, '{}');
        $preferredValues = [];
        if (strpos($routeElement, '|') !== false) {
            list($dynamicRoute, $preferredValuesString) = explode('|', $dynamicRoute);
            $preferredValues = ((strlen($preferredValuesString) > 0) ? explode(',', $preferredValuesString) : []);
        }
        list($paramName, $paramDataType) = explode(':', $dynamicRoute);
        if (!in_array($paramDataType, ['int','string'])) {
            HttpResponse::return5xx(501, 'Invalid datatype set for Route');
            return;
        }
        if (count($preferredValues) > 0 && !in_array($element, $preferredValues)) {
            HttpResponse::return4xx(404, $routeElement);
            return;
        }
        if ($paramDataType === 'int') {
            if (!ctype_digit($element)) {
                HttpResponse::return4xx(404, "Invalid {$paramName}");
                return;
            } else {
                $foundIntRoute = $routeElement;
            }
        } else {
            $foundStringRoute = $routeElement;
        }
        return $paramName;
    }

    /**
     * Validate config file
     *
     * @param array $routes Routes config.
     * @return void
     */
    private static function validateConfigFile(&$routes)
    {
        // Set route code file.
        if (!(isset($routes['__file__']) && (empty($routes['__file__']) || file_exists($routes['__file__'])))) {
            HttpResponse::return5xx(501, 'Missing route configuration file for ' . Constants::$REQUEST_METHOD . ' method');
            return;
        }
        self::$__file__ = $routes['__file__'];
    }

    /**
     * Loads request payoad
     *
     * @return void
     */
    public static function loadPayload()
    {
        $payloadArr = [];
        if (Constants::$REQUEST_METHOD === Constants::$GET) {
            self::urlDecode($_GET);
            $payloadArr = !empty($_GET) ? $_GET : [];
        } else {
            // Load Payload
            parse_str(file_get_contents('php://input'), $payload);
            if (!isset($payload['Payload'])) {
                HttpResponse::return4xx(404, 'Invalid "Payload"');
                return;
            }
            $payloadArr = json_decode($payload['Payload'], true);
            if (is_null($payloadArr)) {
                HttpResponse::return4xx(404, 'Invalid Payload JSON');
                return;
            }
        }
        self::$input['payloadArr'] = $payloadArr;
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
