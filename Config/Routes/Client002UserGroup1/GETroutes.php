<?php
namespace Config\Routes\Client002UserGroup1;

use App\Constants;
use App\Env;
use App\HttpRequest;

return [
    '{table:string}' => [
        '__file__' => Constants::$DOC_ROOT . '/Config/Queries/ClientDB/GET/CRUD.php',
        '{id:int}'  => [
            '__file__' => Constants::$DOC_ROOT . '/Config/Queries/ClientDB/GET/CRUD.php',
        ],
    ],
    'thirdParty' => [
        '{thirdParty:string}' => [
            '__file__' => ''
        ]
    ]
];
