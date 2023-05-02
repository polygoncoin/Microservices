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
        foreach($this->routeElements as $key => $providedUriElementValue) {
            $pos = false;
            if (isset($routes[$providedUriElementValue])) {
                $configuredUri[] = $providedUriElementValue;
                $routes = &$routes[$providedUriElementValue];
                continue;
            } else {
                if (is_array($routes)) {
                    $foundDynamicValues = false;
                    $uriElementConfiguredDetailsArr = [];
                    foreach (array_keys($routes) as $uriElementConfigured) {
                        // Is a dynamic URI element
                        if (strpos($uriElementConfigured, '{') === 0) {
                            // Check for compulsary values
                            $uriElementConfiguredArr = explode('|', $uriElementConfigured);
                            $uriElementConfiguredParamString = $uriElementConfiguredArr[0];
                            $uriElementConfiguredRequiredValuesArr = [];
                            if (isset($uriElementConfiguredArr[1])) {
                                $uriElementConfiguredParamRequiredString = $uriElementConfiguredArr[1];
                                $uriElementConfiguredRequiredValuesArr = explode(',', $uriElementConfiguredParamRequiredString);
                            }
                            $uriElementConfiguredDetails = explode(':', trim($uriElementConfiguredParamString, '{}'));
                            $paramName = $uriElementConfiguredDetails[0];
                            $paramDataType = $uriElementConfiguredDetails[1];
                            if (!in_array($paramDataType, ['int','string'])) {
                                HttpErrorResponse::return5xx(501, 'Invalid datatype set for Route');
                            }
                            $uriElementConfiguredDetailsArr[$paramDataType] = [
                                'configuredCompleteRouteUri' => $uriElementConfigured,
                                'configuredParamName' =>$paramName,
                                'configuredRequiredValues' => $uriElementConfiguredRequiredValuesArr
                            ];
                            $foundDynamicValues = true;
                        }
                    }
                    // Check for dynamic value datatype.
                    if ($foundDynamicValues) {
                        switch (true) {
                            case isset($uriElementConfiguredDetailsArr['int']) && ctype_digit($providedUriElementValue):
                                if (count($uriElementConfiguredDetailsArr['int']['configuredRequiredValues'])>0 && !in_array($providedUriElementValue, $uriElementConfiguredDetailsArr['int']['configuredRequiredValues'])) {
                                    HttpErrorResponse::return4xx(404, $uriElementConfiguredDetailsArr['int']['configuredCompleteRouteUri'], true);
                                }
                                $configuredUri[] = $uriElementConfiguredDetailsArr['int']['configuredCompleteRouteUri'];
                                $this->routeParams[$uriElementConfiguredDetailsArr['int']['configuredParamName']] = (int)$providedUriElementValue;
                                $routes = &$routes[$uriElementConfiguredDetailsArr['int']['configuredCompleteRouteUri']];
                                break;
                            case isset($uriElementConfiguredDetailsArr['string']):
                                if (count($uriElementConfiguredDetailsArr['string']['configuredRequiredValues'])>0 && !in_array($providedUriElementValue, $uriElementConfiguredDetailsArr['string']['configuredRequiredValues'])) {
                                    HttpErrorResponse::return4xx(404, $uriElementConfiguredDetailsArr['string']['configuredCompleteRouteUri'], true);
                                }
                                $configuredUri[] = $uriElementConfiguredDetailsArr['string']['configuredCompleteRouteUri'];
                                $this->routeParams[$uriElementConfiguredDetailsArr['string']['configuredParamName']] = $providedUriElementValue;
                                $routes = &$routes[$uriElementConfiguredDetailsArr['string']['configuredCompleteRouteUri']];
                                break;
                        }
                    } else {
                        HttpErrorResponse::return4xx(404, 'Route not supported');
                    }
                } else {
                    HttpErrorResponse::return4xx(404, 'Route not supported');
                }
            }
        }
        $this->configuredUri = '/' . implode('/', $configuredUri);

        // Set route code file.
        if (isset($routes['__file__']) && file_exists($routes['__file__'])) {
            $this->__file__ = $routes['__file__'];
        } else {
            HttpErrorResponse::return5xx(501, 'Missing route configuration file for' . " {$method} " . 'method');
        }
    }
}
