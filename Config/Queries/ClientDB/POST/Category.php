<?php
namespace Config\Queries\ClientDB\POST;

use App\HttpRequest;

return [
    'query' => "INSERT INTO `{$this->clientDB}`.`category` SET __SET__",
    '__SET__' => [
        //column => [payload|readOnlySession|uriParams|insertIdParams|{custom}, key|{value}],
        'name' => ['payload', 'name', REQUIRED],
        'parent_id' => ['custom', 0],
    ],
    'insertId' => 'category:id',
    'subQuery' => [
        'sub' => [
            'query' => "INSERT INTO `{$this->clientDB}`.`category` SET __SET__",
            '__SET__' => [
                'name' => ['payload', 'subname', REQUIRED],
                'parent_id' => ['insertIdParams', 'category:id'],
            ],
            'insertId' => 'sub:id',
            'subQuery' => [
                'subsub' => [
                    'query' => "INSERT INTO `{$this->clientDB}`.`category` SET __SET__",
                    '__SET__' => [
                        'name' => ['payload', 'subsubname', REQUIRED],
                        'parent_id' => ['insertIdParams', 'sub:id'],
                    ],
                    'insertId' => 'subsub:id',
                ]
            ]
        ]
    ],
    'useHierarchy' => true
];
