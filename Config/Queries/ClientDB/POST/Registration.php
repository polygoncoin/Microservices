<?php
namespace Config\Queries\ClientDB\POST;

use App\HttpRequest;

return [
    'query' => "INSERT INTO `{$this->clientDB}`.`registration` SET __SET__",
    '__CONFIG__' => [// [{payload/uriParams}, key/index, {REQUIRED}]
        ['payload', 'firstname', REQUIRED],
        ['payload', 'lastname', REQUIRED],
        ['payload', 'email', REQUIRED]
    ],
    '__SET__' => [
        //column => [payload|readOnlySession|uriParams|insertIdParams|{custom}, key|{value}],
        'firstname' => ['payload', 'firstname'],
        'lastname' => ['payload', 'lastname'],
        'email' => ['payload', 'email']
    ],
    'insertId' => 'registration:id',
    'subQuery' => [
        'address' => [
            'query' => "INSERT INTO `{$this->clientDB}`.`address` SET __SET__",
            '__CONFIG__' => [// [{payload/uriParams}, key/index, {REQUIRED}]
                ['payload', 'address', REQUIRED]
            ],
            '__SET__' => [
                'registration_id' => ['insertIdParams', 'registration:id'],
                'address' => ['payload', 'address']
            ],
            'insertId' => 'address:id',
        ]
    ]
];
