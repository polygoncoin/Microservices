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
    '{table:string}' => [
        '__file__' => Constants::$DOC_ROOT . '/Config/Queries/ClientDB/POST/CRUD.php',
    ],
    'thirdParty' => [
        '{thirdParty:string}' => [
            '__file__' => ''
        ]
    ],
    'upload' => [
        '{module}:string' => [
            '__file__' => ''
        ]
    ]
];
