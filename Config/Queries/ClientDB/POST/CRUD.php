<?php
namespace Config\Queries\ClientDB\POST;

use App\HttpRequest;

return [
    'registration' => [
        'query' => "INSERT INTO `{$this->clientDB}`.`registration` SET __SET__",
        'payload' => [
            //column => [payload|readOnlySession|uriParams|insertIdParams|{custom}, key|{value}],
            'username' => ['payload', 'username', REQUIRED],
            'password' => ['payload', 'password', REQUIRED],
            'email' => ['custom', date('Y-m-d H:i:s')]
        ],
        'insertId' => 'registration:id',
        'subQuery' => [
            [
                'query' => "INSERT INTO `{$this->clientDB}`.`address` SET __SET__",
                'payload' => [
                    'registration_id' => ['insertIdParams', 'registration:id'],
                    'address' => ['payload', 'address', REQUIRED]
                ]
            ]
        ]
    ],
    'address' => [
        'query' => "INSERT INTO `{$this->clientDB}`.`address` SET __SET__",
        'payload' => [
            //column => [payload|readOnlySession|uriParams|insertIdParams|{custom}, key|{value}],
            'registration' => ['payload', 'registration_id', REQUIRED],
            'address' => ['payload', 'username', REQUIRED],
        ],
        'insertId' => 'address:id'
    ]
][HttpRequest::$input['uriParams']['table']];
