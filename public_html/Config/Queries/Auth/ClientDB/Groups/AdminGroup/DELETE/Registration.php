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

namespace Microservices\public_html\Config\Queries\Auth\ClientDB\Groups\AdminGroup\DELETE;

use Microservices\App\Constants;
use Microservices\App\DatabaseDataTypes;

return array_merge(
    require Constants::$AUTH_QUERIES_DIR .
                DIRECTORY_SEPARATOR . 'ClientDB' .
                DIRECTORY_SEPARATOR . 'Common' .
                DIRECTORY_SEPARATOR . 'Registration.php',
    [
        '__SET__' => [
            [
                'column' => 'is_deleted',
                'fetchFrom' => 'custom',
                'fetchFromValue' => 'Yes'
            ]
        ],
        '__WHERE__' => [
            [
                'column' => 'is_deleted',
                'fetchFrom' => 'custom',
                'fetchFromValue' => 'No'
            ],
            [
                'column' => 'id',
                'fetchFrom' => 'uriParams',
                'fetchFromValue' => 'id',
                'dataType' => DatabaseDataTypes::$PrimaryKey
            ]
        ],
    ]
);

