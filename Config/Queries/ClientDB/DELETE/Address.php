<?php
namespace Microservices\Config\Queries\ClientDB\DELETE;

return array_merge(
    include Constants::$DOC_ROOT . DIRECTORY_SEPARATOR . 'Config' . DIRECTORY_SEPARATOR . 'Queries' . DIRECTORY_SEPARATOR . 'ClientDB' . DIRECTORY_SEPARATOR . 'Common' . DIRECTORY_SEPARATOR . 'Address.php',
    [
        '__SET__' => [
            'is_deleted' => ['custom', 'Yes']
        ],
        '__WHERE__' => [
            'is_deleted' => ['custom', 'No'],
            'id' => ['uriParams', 'id', DatabaseDataTypes::$PrimaryKey]
        ],
    ]
);
