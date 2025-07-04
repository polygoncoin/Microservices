<?php
namespace Microservices\public_html\Config\Routes\Auth\ClientDB\Groups\AdminGroup;

return [
    'registration' => [
        '{id:int|!0}'  => [
            '__FILE__' => $Constants::$PUBLIC_HTML . DIRECTORY_SEPARATOR . 'Config' . DIRECTORY_SEPARATOR . 'Queries' . DIRECTORY_SEPARATOR . 'Auth' . DIRECTORY_SEPARATOR . 'ClientDB' . DIRECTORY_SEPARATOR . 'Groups' . DIRECTORY_SEPARATOR . 'AdminGroup' . DIRECTORY_SEPARATOR . 'PATCH' . DIRECTORY_SEPARATOR . 'Registration.php',
        ],
    ],
    'address' => [
        '{id:int|!0}'  => [
            '__FILE__' => $Constants::$PUBLIC_HTML . DIRECTORY_SEPARATOR . 'Config' . DIRECTORY_SEPARATOR . 'Queries' . DIRECTORY_SEPARATOR . 'Auth' . DIRECTORY_SEPARATOR . 'ClientDB' . DIRECTORY_SEPARATOR . 'Groups' . DIRECTORY_SEPARATOR . 'AdminGroup' . DIRECTORY_SEPARATOR . 'PATCH' . DIRECTORY_SEPARATOR . 'Address.php',
        ],
    ],
    'registration-with-address' => [
        '{id:int|!0}'  => [
            '__FILE__' => $Constants::$PUBLIC_HTML . DIRECTORY_SEPARATOR . 'Config' . DIRECTORY_SEPARATOR . 'Queries' . DIRECTORY_SEPARATOR . 'Auth' . DIRECTORY_SEPARATOR . 'ClientDB' . DIRECTORY_SEPARATOR . 'Groups' . DIRECTORY_SEPARATOR . 'AdminGroup' . DIRECTORY_SEPARATOR . 'PATCH' . DIRECTORY_SEPARATOR . 'Registration-With-Address.php',
        ],
    ],
];
