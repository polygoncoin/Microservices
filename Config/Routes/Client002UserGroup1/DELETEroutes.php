<?php
namespace Config\Routes\Client001UserGroup1;

use App\Constants;
use App\Env;
use App\HttpRequest;

return [
    'registration' => [
        '{id:int}'  => [
            '__file__' => Constants::$DOC_ROOT . '/Config/Queries/ClientDB/DELETE/Registration.php',
        ],
    ],
    'address' => [
        '{id:int}'  => [
            '__file__' => Constants::$DOC_ROOT . '/Config/Queries/ClientDB/DELETE/Address.php',
        ],
    ]
];
