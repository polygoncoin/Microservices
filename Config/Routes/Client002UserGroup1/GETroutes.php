<?php
namespace Config\Routes\Client001UserGroup1;

use App\Constants;
use App\Env;
use App\HttpRequest;

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
