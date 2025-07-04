<?php
namespace Microservices\public_html\Config\Routes\Auth\CommonRoutes\Client;

return [
    'registration' => [
        '{id:int|!0}'  => [
            '__FILE__' => $Constants::$PUBLIC_HTML . DIRECTORY_SEPARATOR . 'Config' . DIRECTORY_SEPARATOR . 'Queries' . DIRECTORY_SEPARATOR . 'Auth' . DIRECTORY_SEPARATOR . 'ClientDB' . DIRECTORY_SEPARATOR . 'Groups' . DIRECTORY_SEPARATOR . 'UserGroup' . DIRECTORY_SEPARATOR . 'DELETE' . DIRECTORY_SEPARATOR . 'Registration.php',
        ],
    ],
    'address' => [
        '{id:int|!0}'  => [
            '__FILE__' => $Constants::$PUBLIC_HTML . DIRECTORY_SEPARATOR . 'Config' . DIRECTORY_SEPARATOR . 'Queries' . DIRECTORY_SEPARATOR . 'Auth' . DIRECTORY_SEPARATOR . 'ClientDB' . DIRECTORY_SEPARATOR . 'Groups' . DIRECTORY_SEPARATOR . 'UserGroup' . DIRECTORY_SEPARATOR . 'DELETE' . DIRECTORY_SEPARATOR . 'Address.php',
        ],
    ],
    'category' => [
        'truncate' => [
            '__FILE__' => $Constants::$PUBLIC_HTML . DIRECTORY_SEPARATOR . 'Config' . DIRECTORY_SEPARATOR . 'Queries' . DIRECTORY_SEPARATOR . 'Auth' . DIRECTORY_SEPARATOR . 'ClientDB' . DIRECTORY_SEPARATOR . 'Groups' . DIRECTORY_SEPARATOR . 'UserGroup' . DIRECTORY_SEPARATOR . 'DELETE' . DIRECTORY_SEPARATOR . 'Category.php',
        ]
    ]
];
