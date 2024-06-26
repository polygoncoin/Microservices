<?php
namespace Microservices\Config\Routes\Common\Client;

use Microservices\App\Constants;
use Microservices\App\Env;
use Microservices\App\HttpRequest;

return [
    'registration' => [
        '{id:int}'  => [
            '__file__' => Constants::$DOC_ROOT . '/Config/Queries/ClientDB/PATCH/Registration.php',
        ],
    ],
    'address' => [
        '{id:int}'  => [
            '__file__' => Constants::$DOC_ROOT . '/Config/Queries/ClientDB/PATCH/Address.php',
        ],
    ]
];
