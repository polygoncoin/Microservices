<?php
namespace Config\Queries\ClientDB\POST;

use App\HttpRequest;

return [
    'registration' => [
        'query' => "INSERT INTO `{$this->clientDB}`.`registration` SET __SET__",
        '__CONFIG__' => [// [{payload/uriParams}, key/index, {REQUIRED}]
            ['payload', 'firstname', REQUIRED],
            ['payload', 'lastname', REQUIRED],
            ['payload', 'email', REQUIRED],
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
                ]
            ]
        ]
    ],
    'address' => [
        'query' => "INSERT INTO `{$this->clientDB}`.`address` SET __SET__",
        '__SET__' => [
            //column => [payload|readOnlySession|uriParams|insertIdParams|{custom}, key|{value}],
            'registration' => ['payload', 'registration_id', REQUIRED],
            'address' => ['payload', 'address', REQUIRED],
        ],
        'insertId' => 'address:id'
    ]
][HttpRequest::$input['uriParams']['table']];
