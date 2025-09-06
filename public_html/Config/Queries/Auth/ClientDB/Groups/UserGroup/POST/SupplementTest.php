<?php
/**
 * API Query config
 * php version 8.3
 *
 * @category  API_Query_Config
 * @package   Microservices
 * @author    Ramesh N Jangid <polygon.co.in@gmail.com>
 * @copyright 2025 Ramesh N Jangid
 * @license   MIT https://opensource.org/license/mit
 * @link      https://github.com/polygoncoin/Microservices
 * @since     Class available since Release 1.0.0
 */
namespace Microservices\public_html\Config\Queries\Auth\ClientDB\Common;

return [
    // Details of data to perform task
    '__PAYLOAD__' => [
        // [
        //     'column' => 'id',
        //     'fetchFrom' => 'uriParams',
        //     'fetchFromValue' => 'id',
        //     'dataType' => DatabaseDataTypes::$PrimaryKey,
        //     'required' => Constants::$REQUIRED
        // ],
        [
            'column' => 'id',
            'fetchFrom' => 'payload',
            'fetchFromValue' => 'payload-id-1',
        ],
        [
            'column' => 'column-1',
            'fetchFrom' => 'payload',
            'fetchFromValue' => 'payload-param-1',
        ],
    ],
    '__FUNCTION__' => 'process',
];
