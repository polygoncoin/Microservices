<?php

/**
 * Route - Available routeArr
 * php version 8.3
 *
 * @category  Route
 * @package   Microservices
 * @author    Ramesh N. Jangid (Sharma) <polygon.co.in@gmail.com>
 * @copyright © 2026 Ramesh N. Jangid (Sharma)
 * @license   MIT https://opensource.org/license/mit
 * @link      https://github.com/polygoncoin/Microservices
 * @since     Class available since Release 1.0.0
 */

namespace Microservices\App;

use Microservices\App\Constant;
use Microservices\App\Env;
use Microservices\App\Http;

/**
 * Route - Available routeArr
 * php version 8.3
 *
 * @category  Route
 * @package   Microservices
 * @author    Ramesh N. Jangid (Sharma) <polygon.co.in@gmail.com>
 * @copyright © 2026 Ramesh N. Jangid (Sharma)
 * @license   MIT https://opensource.org/license/mit
 * @link      https://github.com/polygoncoin/Microservices
 * @since     Class available since Release 1.0.0
 */
class Route
{
	/**
	 * Supported http methods of routeArr
	 *
	 * @var array
	 */
	private $httpMethodArr = [
		'GET',
		'POST',
		'PUT',
		'PATCH',
		'DELETE'
	];

	/**
	 * Route folder
	 *
	 * @var string
	 */
	private $routesFolder = DIRECTORY_SEPARATOR . 'Config'
		. DIRECTORY_SEPARATOR . 'Route';

	/**
	 * Route config ignore key's
	 *
	 * @var array
	 */
	private $reservedKeyArr = ['dataType'];

	/**
	 * Http Object
	 *
	 * @var null|Http
	 */
	private $http = null;

	/**
	 * Constructor
	 *
	 * @param Http $http
	 */
	public function __construct(Http &$http)
	{
		$this->http = &$http;
	}

	/**
	 * Initialize
	 *
	 * @return bool
	 */
	public function init(): bool
	{
		if (Env::$enableRoutesRequest) {
			return true;
		}
		return false;
	}

	/**
	 * Make allowed routeArr list of a logged-in user
	 *
	 * @param array $payload Payload
	 *
	 * @return bool
	 */
	public function process(array $payload = []): bool
	{
		$Constant = __NAMESPACE__ . '\Constant';
		$Env = __NAMESPACE__ . '\Env';

		$httpRouteArr = [];
		if ($this->http->req->isOpenToWebRequest) {
			$userRoutesFolder = Constant::$WWW . $this->routesFolder
				. DIRECTORY_SEPARATOR . 'Open';
		} else {
			$userRoutesFolder = Constant::$WWW . $this->routesFolder
				. DIRECTORY_SEPARATOR . 'Auth'
				. DIRECTORY_SEPARATOR . 'CustomerDB'
				. DIRECTORY_SEPARATOR . 'Groups'
				. DIRECTORY_SEPARATOR . $this->http->req->s['gDetail']['name'];
		}

		foreach ($this->httpMethodArr as $method) {
			$httpRouteArr[$method] = [];
			$routeFileLocation =  $userRoutesFolder
				. DIRECTORY_SEPARATOR . $method . 'routeArr.php';
			if (!file_exists(filename: $routeFileLocation)) {
				continue;
			}
			$routeArr = include $routeFileLocation;
			$route = '';
			$this->getRoutes(
				routeArr: $routeArr,
				route: $route,
				httpRouteArr: $httpRouteArr[$method]
			);
		}
		$this->http->res->dataEncode->addKeyData(
			objectKey: 'Results',
			data: $httpRouteArr
		);

		return true;
	}

	/**
	 * Create Route list
	 *
	 * @param array  $routeArr     Route
	 * @param string $route      Current Route
	 * @param array  $httpRouteArr All http Route
	 *
	 * @return void
	 */
	private function getRoutes(&$routeArr, $route, &$httpRouteArr): void
	{
		foreach ($routeArr as $key => &$r) {
			if (in_array(needle: $key, haystack: $this->reservedKeyArr)) {
				continue;
			}
			if ($key === '__FILE__') {
				$httpRouteArr[] = $route;
			}
			if (is_array(value: $r)) {
				$_route = $route . '/' . $key;
				$this->getRoutes(
					routeArr: $r,
					route: $_route,
					httpRouteArr: $httpRouteArr
				);
			}
		}
	}
}
