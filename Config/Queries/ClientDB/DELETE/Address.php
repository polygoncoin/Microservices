<?php
namespace Microservices\Config\Queries\ClientDB\DELETE;

use Microservices\App\Constants;
use Microservices\App\Env;
use Microservices\App\HttpRequest;

return array_merge(
    include Constants::$DOC_ROOT . '/Config/Queries/ClientDB/Common/Address.php',
    [
        '__CONFIG__' => [// [{payload/uriParams}, key/index, {Constants::$REQUIRED}]
            ['uriParams', 'id', Constants::$REQUIRED],
        ],
        '__SET__' => [
            'is_deleted' => ['custom', 'Yes']
        ],
        '__WHERE__' => [
            'is_deleted' => ['custom', 'No'],
            'id' => ['uriParams', 'id']
        ],
    ]
);
