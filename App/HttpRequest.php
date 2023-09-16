<?php
namespace App;

use App\Constants;
use App\HttpResponse;
use App\Logs;

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
     * Logged-in user request method ID
     *
     * @var int
     */
    public static $httpId = null;

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
     * Initialization
     *
     * @return void
     */
    public static function init()
    {
        self::$REQUEST_METHOD = $_SERVER['REQUEST_METHOD'];
        self::$HTTP_AUTHORIZATION = $_SERVER['HTTP_AUTHORIZATION'];
        self::$REMOTE_ADDR = $_SERVER['REMOTE_ADDR'];
        
        eval('self::$httpId = App\Constants::'.self::$REQUEST_METHOD.';');

        self::loadToken();
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
        } else {
            HttpResponse::return4xx(404, 'Missing token in authorization header');    
        }
        if (empty(self::$input['token'])) {
            HttpResponse::return4xx(404, 'Missing token');
        }
    }

    /**
     * Parse route as per method
     *
     * @return void
     */
    public static function parseRoute()
    {
        $routeFileLocation = __DOC_ROOT__ . '/Config/Routes/' . self::$REQUEST_METHOD . 'routes.php';
        if (file_exists($routeFileLocation)) {
            $routes = require $routeFileLocation;
        } else {
            HttpResponse::return5xx(501, 'Missing route file for' . " {self::$REQUEST_METHOD} " . 'method');
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
        if (self::$REQUEST_METHOD === 'GET') {
            self::$input['payloadArr'] = $_GET;
        } else {
            // Load Payload
            parse_str(file_get_contents('php://input'), $payloadArr);
            if (!isset($payloadArr['data'])) {
                HttpResponse::return4xx(404, 'Invalid data payload');
            }
            self::$input['payloadArr'] = json_decode($payloadArr['data'], true);
        }
        self::$input['payloadType'] = self::payloadType(self::$input['payloadArr']);
        if (self::$input['payloadType']) {
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
