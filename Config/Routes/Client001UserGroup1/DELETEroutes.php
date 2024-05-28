<?php
namespace Config\Routes\Client001UserGroup1;

use App\Constants;
use App\Env;
use App\HttpRequest;

return [
    '{table:string}' => [
        '{id:int}'  => [
            '__file__' => Constants::$DOC_ROOT . '/Config/Queries/ClientDB/DELETE/CRUD.php',
        ],
    ]
];
