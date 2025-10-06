<?php

/**
 * API Route config
 * php version 8.3
 *
 * @category  API_Route_Config
 * @package   Microservices
 * @author    Ramesh N Jangid <polygon.co.in@gmail.com>
 * @copyright 2025 Ramesh N Jangid
 * @license   MIT https://opensource.org/license/mit
 * @link      https://github.com/polygoncoin/Microservices
 * @since     Class available since Release 1.0.0
 */

namespace Microservices\public_html\Config\Routes\Auth\ClientDB\Common\Custom;

return [
    $Env::$customRequestUriPrefix => [
        '{custom:string}' => [
            '__FILE__' => false,
            '{id:int|!0}'  => [
                '__FILE__' => false
            ]
        ]
    ]
];
