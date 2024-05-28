<?php
namespace Config\Routes\AdminGroup;

use App\Constants;
use App\HttpRequest;

return [
    'groups' => [
        '{group_id:int}'  => [
            '__file__' => Constants::$DOC_ROOT . '/Config/Queries/GlobalDB/DELETE/groups.php',
        ],
    ],
    'users' => [
        '{user_id:int}'  => [
            '__file__' => Constants::$DOC_ROOT . '/Config/Queries/GlobalDB/DELETE/users.php',
        ],
    ],
    'connections' => [
        '{connection_id:int}'  => [
            '__file__' => Constants::$DOC_ROOT . '/Config/Queries/GlobalDB/DELETE/connections.php',
        ],
    ],
    'clients' => [
        '{client_id:int}'  => [
            '__file__' => Constants::$DOC_ROOT . '/Config/Queries/GlobalDB/DELETE/clients.php',
        ],
    ],
];
