<?php
namespace Microservices\Config\Routes\Common\Client;

return [
    'category' => [
        '__file__' => $Constants::$DOC_ROOT . '/Config/Queries/ClientDB/GET/Category.php',
        'search' => [
            '__file__' => $Constants::$DOC_ROOT . '/Config/Queries/ClientDB/GET/SearchCategory.php',
        ],
        '{id:int|!0}' => [
            '__file__' => $Constants::$DOC_ROOT . '/Config/Queries/ClientDB/GET/Category-Single.php',
        ]
    ],
    'registration' => [
        '__file__' => $Constants::$DOC_ROOT . '/Config/Queries/ClientDB/GET/Registration-all.php',
        '{id:int|!0}'  => [
            '__file__' => $Constants::$DOC_ROOT . '/Config/Queries/ClientDB/GET/Registration-single.php',
        ],
    ],
    'address' => [
        '__file__' => $Constants::$DOC_ROOT . '/Config/Queries/ClientDB/GET/Address-all.php',
        '{id:int|!0}'  => [
            '__file__' => $Constants::$DOC_ROOT . '/Config/Queries/ClientDB/GET/Address-single.php',
        ],
    ],
    $Env::$routesRequestUri => [
        '__file__' => false,
        '{method:string|GET,POST,PUT,PATCH,DELETE}' => [
            '__file__' => false
        ]
    ]
];
