<?php
namespace Microservices\public_html\Config\Routes\Auth\CommonRoutes\GlobalDB;

return [
    'groups' => [
        '__FILE__' => $Constants::$PUBLIC_HTML . DIRECTORY_SEPARATOR . 'Config' . DIRECTORY_SEPARATOR . 'Queries' . DIRECTORY_SEPARATOR . 'Auth' . DIRECTORY_SEPARATOR . 'GlobalDB' . DIRECTORY_SEPARATOR . 'GET' . DIRECTORY_SEPARATOR . 'groups.php',
        '{group_id:int|!0}'  => [
            '__FILE__' => $Constants::$PUBLIC_HTML . DIRECTORY_SEPARATOR . 'Config' . DIRECTORY_SEPARATOR . 'Queries' . DIRECTORY_SEPARATOR . 'Auth' . DIRECTORY_SEPARATOR . 'GlobalDB' . DIRECTORY_SEPARATOR . 'GET' . DIRECTORY_SEPARATOR . 'groups.php',
        ],
    ],
    'clients' => [
        '__FILE__' => $Constants::$PUBLIC_HTML . DIRECTORY_SEPARATOR . 'Config' . DIRECTORY_SEPARATOR . 'Queries' . DIRECTORY_SEPARATOR . 'Auth' . DIRECTORY_SEPARATOR . 'GlobalDB' . DIRECTORY_SEPARATOR . 'GET' . DIRECTORY_SEPARATOR . 'clients.php',
        '{client_id:int|!0}'  => [
            '__FILE__' => $Constants::$PUBLIC_HTML . DIRECTORY_SEPARATOR . 'Config' . DIRECTORY_SEPARATOR . 'Queries' . DIRECTORY_SEPARATOR . 'Auth' . DIRECTORY_SEPARATOR . 'GlobalDB' . DIRECTORY_SEPARATOR . 'GET' . DIRECTORY_SEPARATOR . 'clients.php',
        ],
    ]
];
