<?php

/**
 * Validator
 * php version 8.3
 *
 * @category  Validator
 * @package   Microservices
 * @author    Ramesh N Jangid <polygon.co.in@gmail.com>
 * @copyright 2025 Ramesh N Jangid
 * @license   MIT https://opensource.org/license/mit
 * @link      https://github.com/polygoncoin/Microservices
 * @since     Class available since Release 1.0.0
 */

namespace Microservices\public_html;

use Microservices\App\Constants;
use Microservices\App\Env;
use Microservices\App\Start;
use Microservices\TestCases\Tests;

ini_set(option: 'display_errors', value: true);
error_reporting(error_level: E_ALL);

define('PUBLIC_HTML', realpath(path: __DIR__ . DIRECTORY_SEPARATOR . '..'));
define('ROUTE_URL_PARAM', 'route');

require_once PUBLIC_HTML . DIRECTORY_SEPARATOR . 'Autoload.php';
spl_autoload_register(callback:  'Microservices\Autoload::register');

// Process the request
$http = [];

$http['server']['host'] = $_SERVER['HTTP_HOST'];
$http['server']['method'] = $_SERVER['REQUEST_METHOD'];
$http['server']['ip'] = $_SERVER['REMOTE_ADDR'];

$http['header'] = getallheaders();
if (isset($_SERVER['Range'])) {
    $http['header']['range'] = $_SERVER['Range'];
}
if (isset($_SERVER['HTTP_USER_AGENT'])) {
    $http['header']['user-agent'] = $_SERVER['HTTP_USER_AGENT'];
}
if (isset($_SERVER['HTTP_AUTHORIZATION'])) {
    $http['header']['authorization'] = $_SERVER['HTTP_AUTHORIZATION'];
}

$http['get'] = &$_GET;
$http['post'] = file_get_contents(filename: 'php://input');
$http['isWebRequest'] = true;

if (
    isset($http['get'][ROUTE_URL_PARAM])
    && in_array(
        needle: $http['get'][ROUTE_URL_PARAM],
        haystack: [
            '/auth-test',
            '/open-test',
            '/open-test-xml',
            '/supp-test'
        ]
    )
    && $http['server']['host'] === 'localhost'
) {
    $tests = new Tests();
    switch ($http['get'][ROUTE_URL_PARAM]) {
        case '/auth-test':
            echo $tests->processAuth();
            break;
        case '/open-test':
            echo $tests->processOpen();
            break;
        case '/open-test-xml':
            echo $tests->processXml();
            break;
        case '/supp-test':
            echo $tests->processSupplement();
            break;
    }
} else {
    // Load .env
    $env = parse_ini_file(filename: PUBLIC_HTML . DIRECTORY_SEPARATOR . '.env');
    foreach ($env as $key => $value) {
        putenv(assignment: "{$key}={$value}");
    }

    Constants::init();
    Env::init(http: $http);

    ob_start();
    [$responseheaders, $responseContent, $responseCode] = Start::http(http: $http, streamData: true);
    ob_clean();

    http_response_code(response_code: $responseCode);
    foreach ($responseheaders as $k => $v) {
        header(header: "{$k}: {$v}");
    }
    die($responseContent);
}
