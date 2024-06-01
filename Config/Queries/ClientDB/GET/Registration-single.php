<?php
namespace Config\Queries\ClientDB\GET;

use App\Constants;
use App\Env;
use App\HttpRequest;

return [
    'query' => "SELECT * FROM `{$Env::$clientDB}`.`registration` WHERE __WHERE__",
    '__CONFIG__' => [// [{payload/uriParams}, key/index, {Constants::$REQUIRED}]
        ['uriParams', 'id', Constants::$REQUIRED],
    ],
    '__WHERE__' => [
        'is_deleted' => ['custom', 'No'],
        'id' => ['uriParams','id']
    ],
    'mode' => 'singleRowFormat'//Single row returned.
];
