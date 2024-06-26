<?php
namespace Microservices\Config\Routes\Common\Client;

use Microservices\App\Constants;
use Microservices\App\Env;
use Microservices\App\HttpRequest;

return [
    'custom' => [
        'category' => [
            '__file__' => '',
        ]
    ],
    'category' => [
        '__file__' => Constants::$DOC_ROOT . '/Config/Queries/ClientDB/GET/Category.php',
        'search' => [
            '__file__' => Constants::$DOC_ROOT . '/Config/Queries/ClientDB/GET/SearchCategory.php',
        ],
    ],
    'registration' => [
        '__file__' => Constants::$DOC_ROOT . '/Config/Queries/ClientDB/GET/Registration-all.php',
        '{id:int}'  => [
            '__file__' => Constants::$DOC_ROOT . '/Config/Queries/ClientDB/GET/Registration-single.php',
        ],
    ],
    'address' => [
        '__file__' => Constants::$DOC_ROOT . '/Config/Queries/ClientDB/GET/Address-all.php',
        '{id:int}'  => [
            '__file__' => Constants::$DOC_ROOT . '/Config/Queries/ClientDB/GET/Address-single.php',
        ],
    ]
];
