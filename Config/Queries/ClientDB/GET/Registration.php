<?php
namespace Config\Queries\ClientDB\GET;

use App\Constants;
use App\Env;
use App\HttpRequest;

//return represents root for hierarchyData
return [
    'query' => "SELECT * FROM `{$Env::$clientDB}`.`registration` WHERE __WHERE__",
    '__WHERE__' => [
        'is_deleted' => ['custom', 'No']
    ],
    'mode' => 'multipleRowFormat',//Multiple rows returned.
    'subQuery' => [
        'reg-address' => [
            'query' => "SELECT * FROM `{$Env::$clientDB}`.`address` WHERE __WHERE__",
            '__WHERE__' => [
                'is_deleted' => ['custom', 'No'],
                'registration_id' => ['hierarchyData', 'root:id'],
            ],
            'mode' => 'multipleRowFormat',//Multiple rows returned.
        ]
    ],
    'useHierarchy' => true
];
