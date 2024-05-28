<?php
namespace Config\Queries\GlobalDB\GET;

use App\Constants;
use App\Env;
use App\HttpRequest;

return [
    'all' => [
        'query' => "SELECT * FROM `{$Env::$globalDB}`.`{$Env::$users}` WHERE __WHERE__ ORDER BY user_id ASC",
        '__WHERE__' => [
            'is_approved' => ['custom', 'Yes'],
            'is_disabled' => ['custom', 'No'],
            'is_deleted' => ['custom', 'No'],
        ],
        'mode' => 'multipleRowFormat'//Multiple rows returned.
    ],
    'single' => [
        'query' => "SELECT * FROM `{$Env::$globalDB}`.`{$Env::$users}` WHERE __WHERE__",
        '__CONFIG__' => [// [{payload/uriParams}, key/index, {Constants::$REQUIRED}]
            ['uriParams', 'user_id', Constants::$REQUIRED],
        ],
        '__WHERE__' => [
            'is_approved' => ['custom', 'Yes'],
            'is_disabled' => ['custom', 'No'],
            'is_deleted' => ['custom', 'No'],
            'user_id' => ['uriParams','user_id']
        ],
        'mode' => 'singleRowFormat'//Single row returned.
    ]
][isset(HttpRequest::$input['uriParams']['user_id'])?'single':'all'];
