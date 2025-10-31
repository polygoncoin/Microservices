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

namespace Microservices\public_html\Config\Routes\Auth\GroupRoutes\AdminGroup;

return [
    'registration' => [
        '__FILE__' => $Constants::$OPEN_QUERIES_DIR .
            DIRECTORY_SEPARATOR . 'POST' .
            DIRECTORY_SEPARATOR . 'Registration.php',
    ],
    'registration-with-address' => [
        '__FILE__' => $Constants::$OPEN_QUERIES_DIR .
            DIRECTORY_SEPARATOR . 'POST' .
            DIRECTORY_SEPARATOR . 'Registration-With-Address.php',
    ],
];
