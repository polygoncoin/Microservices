<?php
namespace Config\Queries\ClientDB\DELETE;

use App\HttpRequest;

return [
    'query' => "UPDATE `{$this->clientDB}`.`".HttpRequest::$input['uriParams']['table']."` SET __SET__ WHERE __WHERE__",
    'payload' => [
        //column => [payload|readOnlySession|uriParams|insertIdParams|{custom}, key|{value}],
        'is_deleted' => ['custom', 'Yes']
    ],
    'where' => [
        'is_deleted' => ['custom', 'No'],
        'id' => ['uriParams', 'id']
    ]
];
