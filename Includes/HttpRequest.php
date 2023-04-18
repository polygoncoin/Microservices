<?php
namespace Includes;
/*
MIT License 

Copyright (c) 2023 Ramesh Narayan Jangid. 

Permission is hereby granted, free of charge, to any person obtaining a copy 
of this software and associated documentation files (the "Software"), to deal 
in the Software without restriction, including without limitation the rights 
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell 
copies of the Software, and to permit persons to whom the Software is 
furnished to do so, subject to the following conditions: 

The above copyright notice and this permission notice shall be included in all 
copies or substantial portions of the Software. 

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR 
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, 
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE 
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER 
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, 
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE 
SOFTWARE. 
*/
/**
 * Class handling details of HTTP request
 *
 * This class is built to process and handle HTTP request
 *
 * @category   Cache
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
     * Locaton of File containing code for route
     *
     * @var array
     */
    public $__file__ = null;

    protected function setToken($authHeader)
    {
        if (preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
            $this->token = $matches[1];
        } else {
            HttpErrorResponse::return404('Missing token in authorization header');    
        }
        if (empty($this->token)) {
            HttpErrorResponse::return404('Missing token');
        }
    }

    /**
     * Parse route as per method
     *
     * @param string $requestMethod HTTP method
     * @param string $requestUri    Requested URI
     * @return void
     */
    protected function parseRoute($requestMethod, $requestUri)
    {
        $this->requestMethod = $requestMethod;

        $routeFileLocation = __DOC_ROOT__ . '/app/routes/' . $this->requestMethod . 'routes.php';
        if (file_exists($routeFileLocation)) {
            $routes = require $routeFileLocation;
        } else {
            HttpErrorResponse::return501('Missing' . ' route file for' . " $this->requestMethod " . 'method');
        }
        $configuredUri = [];
        foreach(explode('/', $requestUri) as $key => $providedUriElementValue) {
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
                        if (strpos($uriElementConfigured, '{')) {
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
                                HttpErrorResponse::return501('Invalid datatype set for Route');
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
                                    HttpErrorResponse::return404($uriElementConfiguredDetailsArr['int']['configuredCompleteRouteUri'], true);
                                }
                                $configuredUri[] = $uriElementConfiguredDetailsArr['int']['configuredCompleteRouteUri'];
                                $this->routeParams[$uriElementConfiguredDetailsArr['int']['configuredParamName']] = (int)$providedUriElementValue;
                                $routes = &$routes[$uriElementConfiguredDetailsArr['int']['configuredCompleteRouteUri']];
                                break;
                            case isset($uriElementConfiguredDetailsArr['string']):
                                if (count($uriElementConfiguredDetailsArr['string']['configuredRequiredValues'])>0 && !in_array($providedUriElementValue, $uriElementConfiguredDetailsArr['string']['configuredRequiredValues'])) {
                                    HttpErrorResponse::return404($uriElementConfiguredDetailsArr['string']['configuredCompleteRouteUri'], true);
                                }
                                $configuredUri[] = $uriElementConfiguredDetailsArr['string']['configuredCompleteRouteUri'];
                                $this->routeParams[$uriElementConfiguredDetailsArr['string']['configuredParamName']] = $providedUriElementValue;
                                $routes = &$routes[$uriElementConfiguredDetailsArr['string']['configuredCompleteRouteUri']];
                                break;
                        }
                    } else {
                        HttpErrorResponse::return404('Route not supported');
                    }
                } else {
                    HttpErrorResponse::return404('Route not supported');
                }
            }
        }
        $this->configuredUri = '/' . implode('/', $configuredUri);
        
        // Set route code file.
        if (isset($routes['__file__']) && file_exists($routes['__file__'])) {
            $this->__file__ = $routes['__file__'];
        } else {
            HttpErrorResponse::return501('Missing route configuration file for' . " $method " . 'method');
        }
    }
}
