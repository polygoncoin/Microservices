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

use function Microservices\Start;

ini_set(option: 'display_errors', value: true);
error_reporting(error_level: E_ALL);

define(
    constant_name: 'PUBLIC_HTML',
    value: realpath(path: __DIR__ . DIRECTORY_SEPARATOR . '..')
);

require_once PUBLIC_HTML . DIRECTORY_SEPARATOR . 'Autoload.php';
spl_autoload_register(callback:  'Microservices\Autoload::register');

require_once  PUBLIC_HTML . DIRECTORY_SEPARATOR . 'Start.php';

// Process the request
$http = [];

$http['server']['host'] = $_SERVER['HTTP_HOST'];
$http['server']['method'] = $_SERVER['REQUEST_METHOD'];
$http['server']['ip'] = $_SERVER['REMOTE_ADDR'];
if (isset($_SERVER['HTTP_AUTHORIZATION'])) {
    $http['header']['authorization'] = $_SERVER['HTTP_AUTHORIZATION'];
}
$http['server']['api_version'] = $_SERVER['HTTP_X_API_VERSION'];
$http['get'] = &$_GET;
$http['isWebRequest'] = true;

// Load .env
$env = parse_ini_file(filename: PUBLIC_HTML . DIRECTORY_SEPARATOR . '.env');
foreach ($env as $key => $value) {
    putenv(assignment: "{$key}={$value}");
}

Start($http, $streamData = true);
