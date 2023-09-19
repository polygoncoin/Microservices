<?php
namespace Config\Queries\ClientDB\PUT;

use App\HttpRequest;

return [
    'registration' => [
        'query' => "UPDATE `{$this->clientDB}`.`".HttpRequest::$input['uriParams']['table']."` SET __SET__ WHERE __WHERE__",
        'payload' => [
            'password' => ['payload', 'password'],
            'email' => ['payload', 'email']
        ],
        'where' => [
            'is_deleted' => ['custom', 'No'],
            'id' => ['uriParams', 'id']
        ]
        ],
    'address' => [
        'query' => "UPDATE `{$this->clientDB}`.`".HttpRequest::$input['uriParams']['table']."` SET __SET__ WHERE __WHERE__",
        'payload' => [
            'address' => ['payload', 'address']
        ],
        'where' => [
            'is_deleted' => ['custom', 'No'],
            'id' => ['uriParams', 'id']
        ]
    ]
][HttpRequest::$input['uriParams']['table']];
