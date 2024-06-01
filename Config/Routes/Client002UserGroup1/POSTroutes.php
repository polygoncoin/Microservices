<?php
namespace Config\Routes\Client001UserGroup1;

use App\Constants;
use App\Env;
use App\HttpRequest;

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
