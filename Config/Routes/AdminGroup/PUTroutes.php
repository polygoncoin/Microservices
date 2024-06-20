<?php
namespace Microservices\Config\Routes\AdminGroup;

use Microservices\App\Constants;
use Microservices\App\Env;
use Microservices\App\HttpRequest;

return [
    'groups' => [
        '{group_id:int}'  => [
            '__file__' => Constants::$DOC_ROOT . '/Config/Queries/GlobalDB/PUT/groups.php',
        ],
    ],
    'users' => [
        '{user_id:int}'  => [
            '__file__' => Constants::$DOC_ROOT . '/Config/Queries/GlobalDB/PUT/users.php',
        ],
    ],
    'connections' => [
        '{connection_id:int}'  => [
            '__file__' => Constants::$DOC_ROOT . '/Config/Queries/GlobalDB/PUT/connections.php',
        ],
    ],
    'clients' => [
        '{client_id:int}'  => [
            '__file__' => Constants::$DOC_ROOT . '/Config/Queries/GlobalDB/PUT/clients.php',
        ],
    ],
];
