<?php
namespace Config\Queries\ClientDB\GET;

use App\Constants;
use App\Env;
use App\HttpRequest;

//return represents root for hierarchyData
return [
    'query' => "SELECT * FROM `{$Env::$clientDB}`.`category` WHERE __WHERE__",
    '__WHERE__' => [
        'is_deleted' => ['custom', 'No'],
        'parent_id' => ['custom', 0]
    ],
    'mode' => 'multipleRowFormat',//Multiple rows returned.
    'subQuery' => [
        'sub' => [
            'query' => "SELECT * FROM `{$Env::$clientDB}`.`category` WHERE __WHERE__",
            '__WHERE__' => [
                'is_deleted' => ['custom', 'No'],
                'parent_id' => ['hierarchyData', 'root:id'],
            ],
            'mode' => 'multipleRowFormat',//Multiple rows returned.
            'subQuery' => [
                'subsub' => [
                    'query' => "SELECT * FROM `{$Env::$clientDB}`.`category` WHERE __WHERE__",
                    '__WHERE__' => [
                        'is_deleted' => ['custom', 'No'],
                        'parent_id' => ['hierarchyData', 'root:sub:id'],
                    ],
                    'mode' => 'multipleRowFormat',//Multiple rows returned.
                    'subQuery' => [
                        'subsubsub' => [
                            'query' => "SELECT * FROM `{$Env::$clientDB}`.`category` WHERE __WHERE__",
                            '__WHERE__' => [
                                'is_deleted' => ['custom', 'No'],
                                'parent_id' => ['hierarchyData', 'root:sub:subsub:id'],//data:address:id
                            ],
                            'mode' => 'multipleRowFormat',//Multiple rows returned.
                        ]
                    ]
                ]
            ],
        ]
    ],
    'useHierarchy' => true
];
