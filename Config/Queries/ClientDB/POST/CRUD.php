<?php
namespace Config\Queries\ClientDB\POST;

use App\Constants;
use App\Env;
use App\HttpRequest;

return [
    'registration' => [
        'query' => "INSERT INTO `{$Env::$clientDB}`.`registration` SET __SET__",
        '__CONFIG__' => [// [{payload/uriParams}, key/index, {Constants::$REQUIRED}]
            ['payload', 'firstname', Constants::$REQUIRED],
            ['payload', 'lastname', Constants::$REQUIRED],
            ['payload', 'email', Constants::$REQUIRED],
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
                'query' => "INSERT INTO `{$Env::$clientDB}`.`address` SET __SET__",
                '__CONFIG__' => [// [{payload/uriParams}, key/index, {Constants::$REQUIRED}]
                    ['payload', 'address', Constants::$REQUIRED]
                ],
                '__SET__' => [
                    'registration_id' => ['insertIdParams', 'registration:id'],
                    'address' => ['payload', 'address']
                ]
            ]
        ]
    ],
    'address' => [
        'query' => "INSERT INTO `{$Env::$clientDB}`.`address` SET __SET__",
        '__SET__' => [
            //column => [payload|readOnlySession|uriParams|insertIdParams|{custom}, key|{value}],
            'registration' => ['payload', 'registration_id', Constants::$REQUIRED],
            'address' => ['payload', 'address', Constants::$REQUIRED],
        ],
        'insertId' => 'address:id'
    ]
][HttpRequest::$input['uriParams']['table']];
