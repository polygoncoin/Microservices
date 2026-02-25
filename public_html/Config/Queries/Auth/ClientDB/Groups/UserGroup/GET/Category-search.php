<?php

/**
 * API Query config
 * php version 8.3
 *
 * @category  API_Query_Config
 * @package   Microservices
 * @author    Ramesh N. Jangid (Sharma) <polygon.co.in@gmail.com>
 * @copyright © 2026 Ramesh N. Jangid (Sharma)
 * @license   MIT https://opensource.org/license/mit
 * @link      https://github.com/polygoncoin/Microservices
 * @since     Class available since Release 1.0.0
 */

namespace Microservices\public_html\Config\Queries\Auth\ClientDB\Groups\UserGroup\GET;

return [
    '__QUERY__' => 'SELECT * FROM `category` WHERE `name` like CONCAT ('%', :name, '%');',
    '__WHERE__' => [
        [
            'column' => 'name',
            'fetchFrom' => 'queryParams',
            'fetchFromValue' => 'name'
        ]
    ],
    '__MODE__' => 'multipleRowFormat',
];
