<?php
namespace App;

use App\HttpErrorResponse;

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
     * Bearer token
     *
     * @var string
     */
    public $token = null;

    /**
     * HTTP request method
     *
     * @var string
     */
    public $requestMethod = null;

    /**
     * Logged-in user request method ID
     *
     * @var int
     */
    public $httpId = null;

    /**
     * Raw route / Configured Uri
     *
     * @var string
     */
    public $configuredUri = '';

    /**
     * Array containing details of route dynamic params
     *
     * @var array
     */
    public $routeParams = [];

    /**
     * Array containing details of received route elements
     *
     * @var array
     */
    public $routeElements = [];

    /**
     * Locaton of File containing code for route
     *
     * @var string
     */
    public $__file__ = null;

    public function checkToken($authHeader)
    {
        if (preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
            $this->token = $matches[1];
        } else {
            HttpErrorResponse::return4xx(404, 'Missing token in authorization header');    
        }
        if (empty($this->token)) {
            HttpErrorResponse::return4xx(404, 'Missing token');
        }
    }

    /**
     * Parse route as per method
     *
     * @param string $requestMethod HTTP method
     * @param string $requestUri    Requested URI
     * @return void
     */
    public function parseRoute($requestMethod, $requestUri)
    {
        $this->requestMethod = $requestMethod;

        $this->httpId = [
            'GET' => 1,
            'POST' => 2,
            'PUT' => 3,
            'PATCH' => 4,
            'DELETE' => 5
        ][$this->requestMethod];

        $routeFileLocation = __DOC_ROOT__ . '/Config/Routes/' . $this->requestMethod . 'routes.php';
        if (file_exists($routeFileLocation)) {
            $routes = require $routeFileLocation;
        } else {
            HttpErrorResponse::return5xx(501, 'Missing route file for' . " $this->requestMethod " . 'method');
        }
        $this->routeElements = explode('/', trim($requestUri, '/'));
        $configuredUri = [];
        foreach($this->routeElements as $key => $e) {
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
                                HttpErrorResponse::return5xx(501, 'Invalid datatype set for Route');
                            }
                            if (count($preferredValues) > 0 && !in_array($e, $preferredValues)) {
                                HttpErrorResponse::return4xx(404, $r);
                            }
                            if ($paramDataType === 'int') {
                                if (!ctype_digit($e)) {
                                    HttpErrorResponse::return4xx(404, "Invalid {$paramName}");
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
                        $this->routeParams[$paramName] = (int)$e;
                    } else if ($foundStringRoute) {
                        $configuredUri[] = $foundStringRoute;
                        $this->routeParams[$paramName] = $e;
                    } else {
                        HttpErrorResponse::return4xx(404, 'Route not supported');
                    }
                } else {
                    HttpErrorResponse::return4xx(404, 'Route not supported');
                }
                $routes = &$routes[(($foundIntRoute) ? $foundIntRoute : $foundStringRoute)];
            }
        }
        $this->configuredUri = '/' . implode('/', $configuredUri);

        // Set route code file.
        if (isset($routes['__file__']) && file_exists($routes['__file__'])) {
            $this->__file__ = $routes['__file__'];
        } elseif ($routes['__file__'] != '') {
            HttpErrorResponse::return5xx(501, 'Missing route configuration file for' . " {$requestMethod} " . 'method');
        }
    }
}
