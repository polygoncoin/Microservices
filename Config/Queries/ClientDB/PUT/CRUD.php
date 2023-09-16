<?php
namespace Config\Queries\ClientDB\PUT;

use App\HttpRequest;

return [
    'm006_master_client' => [
        'query' => "UPDATE `{$this->clientDB}`.`".HttpRequest::$input['uriParams']['table']."` SET __SET__ WHERE __WHERE__",
        'payload' => [
            'name' => ['payload', 'name']
        ],
        'where' => [
            'is_deleted' => ['custom', 'No'],
            'id' => ['uriParams', 'id']
        ]
    ]
][$input['uriParams']['table']];
