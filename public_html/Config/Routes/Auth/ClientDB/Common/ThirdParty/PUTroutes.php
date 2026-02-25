<?php

/**
 * API Route config
 * php version 8.3
 *
 * @category  API_Route_Config
 * @package   Microservices
 * @author    Ramesh N Jangid <polygon.co.in@gmail.com>
 * @copyright © 2026 Ramesh N. Jangid (Sharma)
 * @license   MIT https://opensource.org/license/mit
 * @link      https://github.com/polygoncoin/Microservices
 * @since     Class available since Release 1.0.0
 */

namespace Microservices\public_html\Config\Routes\Auth\ClientDB\Common\ThirdParty;

use Microservices\App\Env;
use Microservices\App\DatabaseDataTypes;

return [
    Env::$thirdPartyRequestRoutePrefix => [
        '{thirdParty:string}' => [
            'dataType' => DatabaseDataTypes::$Default,
            '{id:int}'  => [
                'dataType' => DatabaseDataTypes::$PrimaryKey,
                '__FILE__' => false
            ]
        ]
    ]
];
