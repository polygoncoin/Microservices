<?php
namespace Config\Queries\ClientDB\Common;

use App\HttpRequest;

return [
    'query' => "UPDATE `{$this->clientDB}`.`".HttpRequest::$input['uriParams']['table']."` SET __SET__ WHERE __WHERE__",
    'where' => [
        'is_deleted' => ['custom', 'No'],
        'id' => ['uriParams', 'id']
    ]
];
