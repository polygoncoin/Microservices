<?php
namespace Config\Queries\ClientDB\PATCH;

use App\Constants;
use App\Env;
use App\HttpRequest;

return array_merge(
    include Constants::$DOC_ROOT . '/Config/Queries/ClientDB/Common/Address.php',
    [
        '__CONFIG__' => [// [{payload/uriParams}, key/index, {Constants::$REQUIRED}]
            ['payload', 'address', Constants::$REQUIRED],
            ['uriParams', 'id', Constants::$REQUIRED],
        ],
        '__SET__' => [
            'address' => ['payload', 'address']
        ],
        '__WHERE__' => [
            'is_deleted' => ['custom', 'No'],
            'id' => ['uriParams', 'id']
        ],
    ]
);
