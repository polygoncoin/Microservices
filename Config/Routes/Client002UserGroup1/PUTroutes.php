<?php
namespace Microservices\Config\Routes\Client001UserGroup1;

use Microservices\App\Constants;
use Microservices\App\Env;
use Microservices\App\HttpRequest;

return [
    'registration' => [
        '{id:int}'  => [
            '__file__' => Constants::$DOC_ROOT . '/Config/Queries/ClientDB/PUT/Registration.php',
        ],
    ],
    'address' => [
        '{id:int}'  => [
            '__file__' => Constants::$DOC_ROOT . '/Config/Queries/ClientDB/PUT/Address.php',
        ],
    ]
];
