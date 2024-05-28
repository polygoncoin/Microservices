<?php
namespace Config\Queries\ClientDB\POST;

use App\Constants;
use App\Env;
use App\HttpRequest;

return [
    'query' => "INSERT INTO `{$Env::$clientDB}`.`category` SET __SET__",
    '__CONFIG__' => [// [{payload/uriParams}, key/index, {Constants::$REQUIRED}]
        ['payload', 'name', Constants::$REQUIRED],
    ],
    '__SET__' => [
        //column => [payload|readOnlySession|uriParams|insertIdParams|{custom}, key|{value}],
        'name' => ['payload', 'name'],
        'parent_id' => ['custom', 0],
    ],
    'insertId' => 'category:id',
    'subQuery' => [
        'sub' => [
            'query' => "INSERT INTO `{$Env::$clientDB}`.`category` SET __SET__",
            '__CONFIG__' => [// [{payload/uriParams}, key/index, {Constants::$REQUIRED}]
                ['payload', 'subname', Constants::$REQUIRED],
            ],
            '__SET__' => [
                'name' => ['payload', 'subname'],
                'parent_id' => ['insertIdParams', 'category:id'],
            ],
            'insertId' => 'sub:id',
            'subQuery' => [
                'subsub' => [
                    'query' => "INSERT INTO `{$Env::$clientDB}`.`category` SET __SET__",
                    '__CONFIG__' => [// [{payload/uriParams}, key/index, {Constants::$REQUIRED}]
                        ['payload', 'subsubname', Constants::$REQUIRED],
                    ],
                    '__SET__' => [
                        'name' => ['payload', 'subsubname'],
                        'parent_id' => ['insertIdParams', 'sub:id'],
                    ],
                    'insertId' => 'subsub:id',
                ]
            ]
        ]
    ],
    'useHierarchy' => true
];
