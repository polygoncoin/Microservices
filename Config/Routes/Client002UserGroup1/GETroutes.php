<?php
namespace Microservices\Config\Routes\Client001UserGroup1;

use Microservices\App\Constants;
use Microservices\App\Env;
use Microservices\App\HttpRequest;

return [
    'category' => [
        '__file__' => Constants::$DOC_ROOT . '/Config/Queries/ClientDB/GET/Category.php',
        'search' => [
            '__file__' => Constants::$DOC_ROOT . '/Config/Queries/ClientDB/GET/SearchCategory.php',
        ],
    ],
    'registration' => [
        '__file__' => Constants::$DOC_ROOT . '/Config/Queries/ClientDB/GET/Registration.php',
    ],
];
