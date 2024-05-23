<?php
namespace App;

use App\Constants;
use App\HttpRequest;
use App\HttpResponse;

/**
 * Class to initialize api HTTP request
 *
 * This class process the api request
 *
 * @category   Routes
 * @package    Microservices
 * @author     Ramesh Narayan Jangid
 * @copyright  Ramesh Narayan Jangid
 * @version    Release: @1.0.0@
 * @since      Class available since Release 1.0.0
 */
class Routes
{
    /**
     * Supported HTTP methods of routes
     *
     * @var array
     */
    private $httpMethods = [
        'GET',
        'POST',
        'PUT',
        'PATCH',
        'DELETE'
    ];

    /**
     * Routes folder
     *
     * @var string
     */
    private $routesFolder = __DOC_ROOT__ . '/Config/Routes';

    /**
     * Route config ignore keys
     *
     * @var array
     */
    private $reservedKeys = [];

    /**
     * JsonEncode class object
     *
     * @var object
     */
    public $jsonObj = null;

    /**
     * Initialize
     *
     * @return void
     */
    public function init()
    {
        HttpRequest::init();
        HttpRequest::loadToken();
        HttpRequest::initSession();
        $this->processRoutes();
    }

    /**
     * Make allowed routes list of a logged-in user
     *
     * @return void
     */
    private function processRoutes()
    {
        $httpRoutes = [];
        $userRoutesFolder = $this->routesFolder . '/' . HttpRequest::$input['readOnlySession']['group_name'];
        foreach ($this->httpMethods as $method) {
            $httpRoutes[$method] = [];
            $routeFileLocation =  $userRoutesFolder . '/' . $method . 'routes.php';
            if (!file_exists($routeFileLocation)) {
                continue;
            }
            $routes = require $routeFileLocation;
            $route = '';
            $this->getRoutes($routes, $route, $httpRoutes[$method]);
        }
        $this->jsonObj = HttpResponse::getJsonObject();
        $this->jsonObj->addKeyValue('Results', $httpRoutes);
    }

    /**
     * Create Routes list.
     *
     * @return void
     */
    private function getRoutes(&$routes, $route, &$httpRoutes)
    {
        foreach ($routes as $key => &$r) {
            if (in_array($key, $this->reservedKeys)) {
                continue;
            }
            if (
                HttpRequest::$allowConfigRequest &&
                $key === 'config' &&
                $r === true
            ) {
                $httpRoutes[] = $route . '/' . $key;
            }
            if ($key === '__file__') {
                $httpRoutes[] = $route;
            }
            if (is_array($r)) {
                $_route = $route . '/' . $key;
                $this->getRoutes($r, $_route, $httpRoutes);
            }
        }
    }
}
