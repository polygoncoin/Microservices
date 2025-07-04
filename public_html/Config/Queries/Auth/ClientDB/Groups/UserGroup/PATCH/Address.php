<?php
namespace Microservices\public_html\Config\Queries\Auth\ClientDB\PATCH;

use Microservices\App\Constants;
use Microservices\App\DatabaseDataTypes;

return array_merge(
    include Constants::$PUBLIC_HTML . DIRECTORY_SEPARATOR . 'Config' . DIRECTORY_SEPARATOR . 'Queries' . DIRECTORY_SEPARATOR . 'Auth' . DIRECTORY_SEPARATOR . 'ClientDB' . DIRECTORY_SEPARATOR . 'Common' . DIRECTORY_SEPARATOR . 'Address.php',
    [
        '__SET__' => [
            ['column' => 'address', 'fetchFrom' => 'payload', 'fetchFromValue' => 'address']
        ],
        '__WHERE__' => [
            ['column' => 'is_deleted', 'fetchFrom' => 'custom', 'fetchFromValue' => 'No'],
            ['column' => 'id', 'fetchFrom' => 'uriParams', 'fetchFromValue' => 'id', 'dataType' => DatabaseDataTypes::$PrimaryKey]
        ],
    ]
);
