<?php
namespace Config\Routes\AdminGroup;

use App\Constants;
use App\HttpRequest;

return [
    'groups' => [
        '{group_id:int}'  => [
            '__file__' => Constants::$__DOC_ROOT__ . '/Config/Queries/GlobalDB/PUT/groups.php',
        ],
    ],
    'users' => [
        '{user_id:int}'  => [
            '__file__' => Constants::$__DOC_ROOT__ . '/Config/Queries/GlobalDB/PUT/users.php',
        ],
    ],
    'connections' => [
        '{connection_id:int}'  => [
            '__file__' => Constants::$__DOC_ROOT__ . '/Config/Queries/GlobalDB/PUT/connections.php',
        ],
    ],
    'clients' => [
        '{client_id:int}'  => [
            '__file__' => Constants::$__DOC_ROOT__ . '/Config/Queries/GlobalDB/PUT/clients.php',
        ],
    ],
];
