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
	$env = parse_ini_file(filename: ROOT . DIRECTORY_SEPARATOR . $envFilename);
	foreach ($env as $key => $value) {
		putenv(assignment: "{$key}={$value}");
	}
}

Constant::init();
Env::$timestamp = time();
Env::init();

// Process the request
$httpReqDetailArr = [];

$httpReqDetailArr['streamData'] = true;
$httpReqDetailArr['server']['domainName'] = $_SERVER['HTTP_HOST'];
$httpReqDetailArr['server']['httpMethod'] = $_SERVER['REQUEST_METHOD'];

if (
	((int)getenv('DISABLE_REQUESTS_VIA_PROXIES')) === 1
	&& !isset($_SERVER['REMOTE_ADDR'])
) {
	die("Invalid request");
}

$httpReqDetailArr['server']['httpRequestIP'] = CommonFunction::getHttpRequestIP();

$httpReqDetailArr['header'] = getallheaders();
if (isset($_SERVER['Range'])) {
	$httpReqDetailArr['header']['range'] = $_SERVER['Range'];
}
if (isset($_SERVER['HTTP_USER_AGENT'])) {
	$httpReqDetailArr['header']['userAgent'] = $_SERVER['HTTP_USER_AGENT'];
}
if (isset($_SERVER['HTTP_AUTHORIZATION'])) {
	$httpReqDetailArr['header']['tokenHeader'] = $_SERVER['HTTP_AUTHORIZATION'];
}

$httpReqDetailArr['get'] = &$_GET;
$httpReqDetailArr['post'] = file_get_contents(filename: 'php://input');
$httpReqDetailArr['files'] = [];
if (isset($_FILES)) {
	$httpReqDetailArr['files'] = &$_FILES;
}
$httpReqDetailArr['isWebRequest'] = true;
$httpReqDetailArr['httpRequestHash'] = CommonFunction::httpRequestHash(
	hashArray: [
		$_SERVER['HTTP_ACCEPT_ENCODING'] ?? '',
		$_SERVER['HTTP_ACCEPT_LANGUAGE'] ?? '',
		$_SERVER['HTTP_ACCEPT'] ?? '',
		$_SERVER['HTTP_USER_AGENT'] ?? ''
	]
);

if (
	isset($httpReqDetailArr['get'][ROUTE_URL_PARAM])
	&& in_array(
		needle: $httpReqDetailArr['get'][ROUTE_URL_PARAM],
		haystack: [
			'/tests',
			'/auth-test',
			'/open-test',
			'/open-test-xml',
			'/supp-test'
		]
	)
	&& $httpReqDetailArr['server']['domainName'] === 'localhost'
) {
	$tests = new Test();
	switch ($httpReqDetailArr['get'][ROUTE_URL_PARAM]) {
		case '/tests':
			echo '<pre>'.print_r(value: $tests->processTests(), return: true);
			break;
		case '/auth-test':
			echo '<pre>'.print_r(value: $tests->processAuth(), return: true);
			break;
		case '/open-test':
			echo '<pre>'.print_r(value: $tests->processOpen(), return: true);
			break;
		case '/open-test-xml':
			echo '<pre>'.print_r(value: $tests->processXml(), return: true);
			break;
		case '/supp-test':
			echo '<pre>'.print_r(value: $tests->processSupplement(), return: true);
			break;
	}
} else {

	ob_start();
	[$responseHeaderArr, $responseContent, $responseCode] = Start::http(httpReqDetailArr: $httpReqDetailArr);
	@ob_clean();

	http_response_code(response_code: $responseCode);
	foreach ($responseHeaderArr as $k => $v) {
		header(header: "{$k}: {$v}");
	}
	die($responseContent);
}
