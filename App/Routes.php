<?php
namespace Microservices\App;

use Microservices\App\Constants;
use Microservices\App\Common;
use Microservices\App\Env;

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
    private $routesFolder = DIRECTORY_SEPARATOR . 'Config' . DIRECTORY_SEPARATOR . 'Routes';

    /**
     * Route config ignore keys
     *
     * @var array
     */
    private $reservedKeys = [];

    /**
     * Microservices Collection of Common Objects
     *
     * @var null|Common
     */
    private $c = null;

    /**
     * Constructor
     *
     * @param Common $common
     */
    public function __construct(&$common)
    {
        $this->c = &$common;
    }

    /**
     * Initialize
     *
     * @return boolean
     */
    public function init()
    {
        if (Env::$allowRoutesRequest) {
            return true;
        }
        return false;
    }

    /**
     * Make allowed routes list of a logged-in user
     *
     * @return boolean
     */
    public function process()
    {
        $Constants = __NAMESPACE__ . '\Constants';
        $Env = __NAMESPACE__ . '\Env';

        $httpRoutes = [];
        if ($this->c->httpRequest->open) {
            $userRoutesFolder = Constants::$PUBLIC_HTML . $this->routesFolder . DIRECTORY_SEPARATOR . 'Open';
        } else {
            $userRoutesFolder = Constants::$PUBLIC_HTML . $this->routesFolder . DIRECTORY_SEPARATOR . 'Auth' . DIRECTORY_SEPARATOR . 'GroupRoutes' .  DIRECTORY_SEPARATOR . $this->c->httpRequest->session['groupDetails']['name'];
        }

        foreach ($this->httpMethods as $method) {
            $httpRoutes[$method] = [];
            $routeFileLocation =  $userRoutesFolder . DIRECTORY_SEPARATOR . $method . 'routes.php';
            if (!file_exists($routeFileLocation)) {
                continue;
            }
            $routes = include $routeFileLocation;
            $route = '';
            $this->getRoutes($routes, $route, $httpRoutes[$method]);
        }
        $this->c->httpResponse->dataEncode->addKeyData('Results', $httpRoutes);

        return true;
    }

    /**
     * Create Routes list
     *
     * @return void
     */
    private function getRoutes(&$routes, $route, &$httpRoutes)
    {
        foreach ($routes as $key => &$r) {
            if (in_array($key, $this->reservedKeys)) {
                continue;
            }
            if ($key === '__FILE__') {
                $httpRoutes[] = $route;
            }
            if (is_array($r)) {
                $_route = $route . '/' . $key;
                $this->getRoutes($r, $_route, $httpRoutes);
            }
        }
    }
}
