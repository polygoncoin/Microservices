<?php
namespace Config\Queries\ClientDB\GET;

use App\Constants;
use App\Env;
use App\HttpRequest;

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
