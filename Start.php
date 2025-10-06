<?php

/**
 * Start
 * php version 8.3
 *
 * @category  Start
 * @package   Microservices
 * @author    Ramesh N Jangid <polygon.co.in@gmail.com>
 * @copyright 2025 Ramesh N Jangid
 * @license   MIT https://opensource.org/license/mit
 * @link      https://github.com/polygoncoin/Microservices
 * @since     Class available since Release 1.0.0
 */

namespace Microservices;

use Microservices\App\Logs;
use Microservices\App\DataRepresentation\DataEncode;
use Microservices\Microservices;

require_once __DIR__ . DIRECTORY_SEPARATOR . 'Autoload.php';

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

try {
    // Check version
    if (
        (
            !isset($http['server'])
            && !isset($http['server']['api_version'])
        )
        || $http['server']['api_version'] !== 'v1.0.0'
    ) {
        // Set response headers
        header(header: 'Content-Type: application/json; charset=utf-8');
        header(header: 'Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
        header(header: 'Pragma: no-cache');

        die('{"Status": 400, "Message": "Bad Request"}');
    }

    // Load .env
    $env = parse_ini_file(filename: __DIR__ . DIRECTORY_SEPARATOR . '.env');
    foreach ($env as $key => $value) {
        putenv(assignment: "{$key}={$value}");
    }

    $Microservices = new Microservices(http: $http);

    if ($http['server']['method'] == 'OPTIONS') {
        // Setting CORS
        foreach ($Microservices->getHeaders() as $k => $v) {
            header(header: "{$k}: {$v}");
        }
        exit();
    }

    if ($Microservices->init()) {
        // Setting CORS
        foreach ($Microservices->getHeaders() as $k => $v) {
            header(header: "{$k}: {$v}");
        }
        $Microservices->process();
        $Microservices->outputResults();
    }
} catch (\Exception $e) {
    if (!in_array(needle: $e->getCode(), haystack: [400, 429])) {
        list($usec, $sec) = explode(separator: ' ', string: microtime());
        $dateTime = date(
            format: 'Y-m-d H:i:s',
            timestamp: $sec
        ) . substr(string: $usec, offset: 1);

        // Log request details
        $logDetails = [
            'LogType' => 'ERROR',
            'DateTime' => $dateTime,
            'HttpDetails' => [
                'HttpCode' => $e->getCode(),
                'HttpMessage' => $e->getMessage()
            ],
            'Details' => [
                '$_GET' => $_GET,
                'php:input' => @file_get_contents(filename: 'php://input'),
                'session' => $Microservices->c->req->s
            ]
        ];
        (new Logs)->log(logDetails: $logDetails);
    }

    // Set response code
    http_response_code(response_code: $e->getCode());

    if ($e->getCode() == 429) {
        header(header: "Retry-After: {$e->getMessage()}");
        $arr = [
            'Status' => $e->getCode(),
            'Message' => 'Too Many Requests',
            'RetryAfter' => $e->getMessage()
        ];
    } else {
        $arr = [
            'Status' => $e->getCode(),
            'Message' => $e->getMessage()
        ];
    }

    $dataEncode = new DataEncode(http: $http);
    $dataEncode->init();
    $dataEncode->startObject();
    $dataEncode->addKeyData(key: 'Error', data: $arr);
    $dataEncode->streamData();
}
