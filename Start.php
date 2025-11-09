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

use Microservices\App\Common;
use Microservices\App\Logs;
use Microservices\App\DataRepresentation\DataEncode;
use Microservices\Microservices;

class Start
{
    /**
     * Process http request data
     *
     * @param array $http       HTTP request details
     * @param bool  $streamData false - represent child request
     *
     * @return array
     */
    public static function http($http, $streamData = false)
    {
        try {
            // Check version
            if (
                !isset($http['server']['api_version'])
                || $http['server']['api_version'] !== 'v1.0.0'
            ) {
                if ($streamData) {
                    // Set response headers
                    header(header: 'Content-Type: application/json; charset=utf-8');
                    header(header: 'Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
                    header(header: 'Pragma: no-cache');
                }
                die('{"Status": 400, "Message": "Bad Request"}');
            }

            $Microservices = new Microservices(http: $http);

            if ($streamData && $http['server']['method'] == 'OPTIONS') {
                // Setting CORS
                foreach ($Microservices->getHeaders() as $k => $v) {
                    header(header: "{$k}: {$v}");
                }
                exit();
            }

            if ($Microservices->init()) {
                // Setting CORS
                if ($streamData) {
                    foreach ($Microservices->getHeaders() as $k => $v) {
                        header(header: "{$k}: {$v}");
                    }
                }

                $Microservices->process();
                if ($streamData) {
                    $Microservices->outputResults();
                } else {
                    return $Microservices->returnResults();
                }
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
                        'session' => Common::$req->s
                    ]
                ];
                $logsObj = new Logs();
                $logsObj->log(logDetails: $logDetails);
            }

            // Set response code
            if ($streamData) {
                http_response_code(response_code: $e->getCode());
            }

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

            if ($streamData) {
                $dataEncode->streamData();
            } else {
                return $dataEncode->getData();
            }
        }
    }
}
