<?php
namespace Microservices\public_html\Config\Routes\Auth\CommonRoutes\GlobalDB;

return [
    'group' => [
        '{group_id:int|!0}'  => [
            '__FILE__' => $Constants::$PUBLIC_HTML . DIRECTORY_SEPARATOR . 'Config' . DIRECTORY_SEPARATOR . 'Queries' . DIRECTORY_SEPARATOR . 'Auth' . DIRECTORY_SEPARATOR . 'GlobalDB' . DIRECTORY_SEPARATOR . 'DELETE' . DIRECTORY_SEPARATOR . 'groups.php',
        ],
    ],
    'client' => [
        '{client_id:int|!0}'  => [
            '__FILE__' => $Constants::$PUBLIC_HTML . DIRECTORY_SEPARATOR . 'Config' . DIRECTORY_SEPARATOR . 'Queries' . DIRECTORY_SEPARATOR . 'Auth' . DIRECTORY_SEPARATOR . 'GlobalDB' . DIRECTORY_SEPARATOR . 'DELETE' . DIRECTORY_SEPARATOR . 'clients.php',
        ],
    ],
];
