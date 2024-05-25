<?php
namespace Config\Queries\ClientDB\GET;

use App\HttpRequest;

return [
    'all' => [
        'countQuery' => "SELECT count(1) as `count` FROM `{$this->clientDB}`.`".HttpRequest::$input['uriParams']['table']."` WHERE __WHERE__",
        'query' => "SELECT * FROM `{$this->clientDB}`.`".HttpRequest::$input['uriParams']['table']."` WHERE __WHERE__",
        '__WHERE__' => [
            'is_deleted' => ['custom', 'No']
        ],
        'mode' => 'multipleRowFormat'//Multiple rows returned.
    ],
    'single' => [
        'query' => "SELECT * FROM `{$this->clientDB}`.`".HttpRequest::$input['uriParams']['table']."` WHERE __WHERE__",
        '__CONFIG__' => [// [{payload/uriParams}, key/index, {REQUIRED}]
            ['uriParams', 'id', REQUIRED],
        ],
        '__WHERE__' => [
            'is_deleted' => ['custom', 'No'],
            'id' => ['uriParams','id']
        ],
        'mode' => 'singleRowFormat'//Single row returned.
    ]
][isset(HttpRequest::$input['uriParams']['id'])?'single':'all'];
