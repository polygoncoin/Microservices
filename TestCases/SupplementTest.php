<?php

/**
 * TestCases
 * php version 8.3
 *
 * @category  TestCases
 * @package   Microservices
 * @author    Ramesh N Jangid <polygon.co.in@gmail.com>
 * @copyright 2025 Ramesh N Jangid
 * @license   MIT https://opensource.org/license/mit
 * @link      https://github.com/polygoncoin/Microservices
 * @since     Class available since Release 1.0.0
 */

namespace Microservices\TestCases;

require_once __DIR__ . DIRECTORY_SEPARATOR . 'TestFunctions.php';

$apiVersion = 'X-API-Version: v1.0.0';
$cacheControl = 'Cache-Control: no-cache';
$contentType = 'Content-Type: text/plain; charset=utf-8';

$defaultHeaders = [];
$defaultHeaders[] = $apiVersion;
$defaultHeaders[] = $cacheControl;

$response = [];

$homeURL = 'http://api.client001.localhost/Microservices/public_html/index.php';

$response[] = include GET . DIRECTORY_SEPARATOR . 'Reload.php';

// Client login
$payload = [
    'username' => 'client_1_group_1_user_1',
    'password' => 'shames11'
];
$response[] = include POST . DIRECTORY_SEPARATOR . 'Login.php';

$response[] = include POST . DIRECTORY_SEPARATOR . 'SupplementTest.php';

echo '<pre>';
print_r(value: $response);
