<?php
namespace Microservices\Config\Routes\Client001UserGroup1;

use Microservices\App\Constants;
use Microservices\App\Env;
use Microservices\App\HttpRequest;

return [
    'category' => [
        '__file__' => Constants::$DOC_ROOT . '/Config/Queries/ClientDB/POST/Category.php',
        'config' => true
    ],
    'registration' => [
        '__file__' => Constants::$DOC_ROOT . '/Config/Queries/ClientDB/POST/Registration.php',
        'config' => true
    ],
    'address' => [
        '__file__' => Constants::$DOC_ROOT . '/Config/Queries/ClientDB/POST/Address.php',
        'config' => true
    ],
];
