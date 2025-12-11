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

// Load .env
$env = parse_ini_file(filename: PUBLIC_HTML . DIRECTORY_SEPARATOR . '.env');
foreach ($env as $key => $value) {
    putenv(assignment: "{$key}={$value}");
}

// Process the request
$http = [];

$http['server']['host'] = $_SERVER['HTTP_HOST'];
$http['server']['method'] = $_SERVER['REQUEST_METHOD'];

if (
    ((int)getenv('DISABLE_REQUESTS_VIA_PROXIES')) === 1
    && !isset($_SERVER['REMOTE_ADDR'])
) {
    die("Invalid request");
}

$http['server']['ip'] = getVisitorIP();

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
$http['files'] = [];
if (isset($_FILES)) {
    $http['files'] = &$_FILES;
}
$http['isWebRequest'] = true;

require_once PUBLIC_HTML . DIRECTORY_SEPARATOR . 'Autoload.php';
spl_autoload_register(callback:  'Microservices\Autoload::register');

Constants::init();
Env::$timestamp = time();
Env::init(http: $http);

if (
    isset($http['get'][ROUTE_URL_PARAM])
    && in_array(
        needle: $http['get'][ROUTE_URL_PARAM],
        haystack: [
            '/tests',
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
    [$responseheaders, $responseContent, $responseCode] = Start::http(http: $http, streamData: true);
    ob_clean();

    http_response_code(response_code: $responseCode);
    foreach ($responseheaders as $k => $v) {
        header(header: "{$k}: {$v}");
    }
    die($responseContent);
}
function getVisitorIP() {
    // Check for shared internet connections (e.g., Cloudflare, proxy)
    if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
        $ip = $_SERVER['HTTP_CLIENT_IP'];
    }
    // Check if the user is behind a proxy and the IP is forwarded
    elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        // HTTP_X_FORWARDED_FOR can contain a comma-separated list of IPs
        // The first one is typically the original client IP
        $ipList = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
        $ip = trim($ipList[0]);
    }
    // Default method: get the remote address directly
    else {
        $ip = $_SERVER['REMOTE_ADDR'];
    }
    return $ip;
}
