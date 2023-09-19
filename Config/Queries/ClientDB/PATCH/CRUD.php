<?php
namespace Config\Queries\ClientDB\PATCH;

use App\HttpRequest;

return [
    'registration' => [
        'query' => "UPDATE `{$this->clientDB}`.`".HttpRequest::$input['uriParams']['table']."` SET __SET__ WHERE __WHERE__",
        'payload' => [
            'username' => ['payload', 'username']
        ],
        'where' => [
            'is_deleted' => ['custom', 'No'],
            'id' => ['uriParams', 'id']
        ]
    ]
][HttpRequest::$input['uriParams']['table']];
