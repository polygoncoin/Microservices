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

use Microservices\App\Start;
use Microservices\TestCases\Tests;

ini_set(option: 'display_errors', value: true);
error_reporting(error_level: E_ALL);

define('PUBLIC_HTML', realpath(path: __DIR__ . DIRECTORY_SEPARATOR . '..'));

require_once PUBLIC_HTML . DIRECTORY_SEPARATOR . 'Autoload.php';
spl_autoload_register(callback:  'Microservices\Autoload::register');

// Process the request
$http = [];

$http['server']['host'] = $_SERVER['HTTP_HOST'];
$http['server']['method'] = $_SERVER['REQUEST_METHOD'];
$http['server']['ip'] = $_SERVER['REMOTE_ADDR'];
if (isset($_SERVER['HTTP_AUTHORIZATION'])) {
    $http['header']['authorization'] = $_SERVER['HTTP_AUTHORIZATION'];
}
$http['get'] = &$_GET;
$http['isWebRequest'] = true;

if (
    isset($http['get']['r'])
    && in_array(
        needle: $http['get']['r'],
        haystack: [
            '/auth-test',
            '/open-test',
            '/open-test-xml',
            '/supp-test'
        ]
    )
) {
    $tests = new Tests();
    switch ($http['get']['r']) {
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

    [$responseheaders, $responseContent, $responseCode] = Start::http(http: $http, streamData: true);

    http_response_code(response_code: $responseCode);
    foreach ($responseheaders as $k => $v) {
        header(header: "{$k}: {$v}");
    }
    die($responseContent);
}
