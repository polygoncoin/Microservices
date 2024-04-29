<?php
namespace Config\Queries\ClientDB\POST;

use App\HttpRequest;

return [
    'registration' => [
        'query' => "INSERT INTO `{$this->clientDB}`.`registration` SET __SET__",
        'payload' => [
            //column => [payload|readOnlySession|uriParams|insertIdParams|{custom}, key|{value}],
            'firstname' => ['payload', 'firstname', REQUIRED],
            'lastname' => ['payload', 'lastname', REQUIRED],
            'email' => ['payload', 'email', REQUIRED]
        ],
        'insertId' => 'registration:id',
        'subQuery' => [
            'address' => [
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
            'address' => ['payload', 'address', REQUIRED],
        ],
        'insertId' => 'address:id'
    ]
][HttpRequest::$input['uriParams']['table']];
