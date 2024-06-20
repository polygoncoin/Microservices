<?php
namespace Microservices\Config\Queries\ClientDB\GET;

use Microservices\App\Constants;
use Microservices\App\Env;
use Microservices\App\HttpRequest;

//return represents root for hierarchyData
return [
    'query' => "SELECT * FROM `{$Env::$clientDB}`.`category` WHERE `name` like CONCAT ('%', :name, '%');",
    '__CONFIG__' => [// [{payload/uriParams}, key/index, {Constants::$REQUIRED}]
        ['payload', 'name', Constants::$REQUIRED],
    ],
    '__WHERE__' => [
        'name' => ['payload', 'name']
    ],
    'mode' => 'multipleRowFormat',//Multiple rows returned.
];
