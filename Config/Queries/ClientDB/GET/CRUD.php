<?php
namespace Config\Queries\ClientDB\GET;

use App\Constants;
use App\Env;
use App\HttpRequest;

return [
    'all' => [
        'countQuery' => "SELECT count(1) as `count` FROM `{$Env::$clientDB}`.`{$HttpRequest::$input['uriParams']['table']}` WHERE __WHERE__",
        'query' => "SELECT * FROM `{$Env::$clientDB}`.`{$HttpRequest::$input['uriParams']['table']}` WHERE __WHERE__",
        '__WHERE__' => [
            'is_deleted' => ['custom', 'No']
        ],
        'mode' => 'multipleRowFormat'//Multiple rows returned.
    ],
    'single' => [
        'query' => "SELECT * FROM `{$Env::$clientDB}`.`{$HttpRequest::$input['uriParams']['table']}` WHERE __WHERE__",
        '__CONFIG__' => [// [{payload/uriParams}, key/index, {Constants::$REQUIRED}]
            ['uriParams', 'id', Constants::$REQUIRED],
        ],
        '__WHERE__' => [
            'is_deleted' => ['custom', 'No'],
            'id' => ['uriParams','id']
        ],
        'mode' => 'singleRowFormat'//Single row returned.
    ]
][isset(HttpRequest::$input['uriParams']['id'])?'single':'all'];
