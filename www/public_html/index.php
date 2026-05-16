<?php

/**
 * Validator
 * php version 8.3
 *
 * @category  Validator
 * @package   Microservices
 * @author    Ramesh N. Jangid (Sharma) <polygon.co.in@gmail.com>
 * @copyright © 2026 Ramesh N. Jangid (Sharma)
 * @license   MIT https://opensource.org/license/mit
 * @link      https://github.com/polygoncoin/Microservices
 * @since     Class available since Release 1.0.0
 */

namespace Microservices\www\public_html;

use Microservices\App\Constant;
use Microservices\App\Env;
use Microservices\App\CommonFunction;
use Microservices\App\HttpStatus;
use Microservices\App\SessionHandler\Session;
use Microservices\App\Reload;
use Microservices\App\Start;
use Microservices\TestCase\Test;

ini_set(option: 'display_errors', value: true);
error_reporting(error_level: E_ALL);

define('ROOT', realpath(path: __DIR__ . '/../../'));
define('ROUTE_URL_PARAM', 'route');

require_once ROOT . DIRECTORY_SEPARATOR . 'Autoload.php';
spl_autoload_register(callback:  'Microservices\Autoload::register');

// Load .env(s)
foreach ([
	'.env',
	'.env.cidr',
	'.env.customer.container',
	'.env.enable',
	'.env.global.container',
	'.env.rateLimiting'
] as $envFilename) {
	$envDataArr = parse_ini_file(filename: ROOT . DIRECTORY_SEPARATOR . $envFilename);
	foreach ($envDataArr as $envVarName => $envVarValue) {
		putenv(assignment: "{$envVarName}={$envVarValue}");
	}
}

Constant::init();
Env::$timestamp = time();
Env::init();

if (Env::$authMode === 'Session') {
	// Initialize Session Handler
	Session::initSessionHandler(sessionMode: Env::$sessionMode, options: []);

	// Start session in readonly mode
	Session::sessionStartReadonly();
}

// Process the request
$httpReqData = [];

$httpReqData['streamData'] = true;
$httpReqData['server']['domainName'] = $_SERVER['HTTP_HOST'];
$httpReqData['server']['httpMethod'] = $_SERVER['REQUEST_METHOD'];

if (
	((int)getenv('DISABLE_REQUESTS_VIA_PROXIES')) === 1
	&& !isset($_SERVER['REMOTE_ADDR'])
) {
	die("Invalid request");
}

$httpReqData['server']['httpRequestIP'] = CommonFunction::getHttpRequestIp();

$httpReqData['header'] = getallheaders();
if (isset($_SERVER['Range'])) {
	$httpReqData['header']['range'] = $_SERVER['Range'];
}
if (isset($_SERVER['HTTP_USER_AGENT'])) {
	$httpReqData['header']['userAgent'] = $_SERVER['HTTP_USER_AGENT'];
}
if (isset($_SERVER['HTTP_AUTHORIZATION'])) {
	$httpReqData['header']['tokenHeader'] = $_SERVER['HTTP_AUTHORIZATION'];
}

$httpReqData['get'] = &$_GET;
if (isset($httpReqData['get'][ROUTE_URL_PARAM])) {
	$httpReqData['get'][ROUTE_URL_PARAM] = '/' . trim(
		string: $httpReqData['get'][ROUTE_URL_PARAM],
		characters: '/'
	);
} else {
	throw new \Exception(
		message: 'Missing route',
		code: HttpStatus::$NotFound
	);
}

$httpReqData['post'] = file_get_contents(filename: 'php://input');
$httpReqData['files'] = [];
if (isset($_FILES)) {
	$httpReqData['files'] = &$_FILES;
}
$httpReqData['isWebRequest'] = true;
$httpReqData['httpRequestHash'] = CommonFunction::httpRequestHash(
	hashArray: [
		$_SERVER['HTTP_ACCEPT_ENCODING'] ?? '',
		$_SERVER['HTTP_ACCEPT_LANGUAGE'] ?? '',
		$_SERVER['HTTP_ACCEPT'] ?? '',
		$_SERVER['HTTP_USER_AGENT'] ?? ''
	]
);

if (
	isset($httpReqData['get'][ROUTE_URL_PARAM])
	&& in_array(
		needle: $httpReqData['get'][ROUTE_URL_PARAM],
		haystack: [
			'/all-test',
			'/auth-test',
			'/open-test',
			'/open-test-xml',
			'/supp-test'
		]
	)
	&& $httpReqData['server']['domainName'] === 'localhost'
) {
	$testObj = new Test();
	switch ($httpReqData['get'][ROUTE_URL_PARAM]) {
		case '/all-test':
			echo '<pre>'.print_r(value: $testObj->processAllTest(), return: true);
			break;
		case '/auth-test':
			echo '<pre>'.print_r(value: $testObj->processPrivate(), return: true);
			break;
		case '/open-test':
			echo '<pre>'.print_r(value: $testObj->processPublic(), return: true);
			break;
		case '/open-test-xml':
			echo '<pre>'.print_r(value: $testObj->processPublicXml(), return: true);
			break;
		case '/supp-test':
			echo '<pre>'.print_r(value: $testObj->processPrivateSupplement(), return: true);
			break;
	}
} else {

	if ($httpReqData['get'][ROUTE_URL_PARAM] === '/' . Env::$reloadRequestRoutePrefix) {
		Reload::process();
		return false;
	} else {

		ob_start();
		[$responseHeaderArr, $responseContent, $responseCode] = Start::http(httpReqData: $httpReqData);
		@ob_clean();

		$responseCode = $responseCode ?? 200;
		http_response_code(response_code: $responseCode);

		foreach ($responseHeaderArr as $headerName => $headerValue) {
			header(header: "{$headerName}: {$headerValue}");
		}

		die($responseContent);
	}
}
