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
namespace Microservices\public_html\Config\Routes\Auth\CommonRoutes\GlobalDB;

return [
    'group' => [
        '__FILE__' => $Constants::$PUBLIC_HTML .
            DIRECTORY_SEPARATOR . 'Config' .
            DIRECTORY_SEPARATOR . 'Queries' .
            DIRECTORY_SEPARATOR . 'Auth' .
            DIRECTORY_SEPARATOR . 'GlobalDB' .
            DIRECTORY_SEPARATOR . 'POST' .
            DIRECTORY_SEPARATOR . 'groups.php',
    ],
    'client' => [
        '__FILE__' => $Constants::$PUBLIC_HTML .
            DIRECTORY_SEPARATOR . 'Config' .
            DIRECTORY_SEPARATOR . 'Queries' .
            DIRECTORY_SEPARATOR . 'Auth' .
            DIRECTORY_SEPARATOR . 'GlobalDB' .
            DIRECTORY_SEPARATOR . 'POST' .
            DIRECTORY_SEPARATOR . 'clients.php',
    ],
];
