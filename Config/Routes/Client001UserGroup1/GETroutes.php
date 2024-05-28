<?php
namespace Config\Routes\Client001UserGroup1;

use App\Constants;
use App\HttpRequest;

return [
    'category' => [
        '__file__' => Constants::$DOC_ROOT . '/Config/Queries/ClientDB/GET/Category.php',
    ],
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
