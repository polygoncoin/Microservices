<?php
namespace Config\Routes\AdminGroup;

use App\Constants;
use App\HttpRequest;

return [
    'groups' => [
        '__file__' => Constants::$__DOC_ROOT__ . '/Config/Queries/GlobalDB/GET/groups.php',
        '{group_id:int}'  => [
            '__file__' => Constants::$__DOC_ROOT__ . '/Config/Queries/GlobalDB/GET/groups.php',
        ],
    ],
    'users' => [
        '__file__' => Constants::$__DOC_ROOT__ . '/Config/Queries/GlobalDB/GET/users.php',
        '{user_id:int}'  => [
            '__file__' => Constants::$__DOC_ROOT__ . '/Config/Queries/GlobalDB/GET/users.php',
        ],
    ],
    'connections' => [
        '__file__' => Constants::$__DOC_ROOT__ . '/Config/Queries/GlobalDB/GET/connections.php',
        '{connection_id:int}'  => [
            '__file__' => Constants::$__DOC_ROOT__ . '/Config/Queries/GlobalDB/GET/connections.php',
        ],
    ],
    'clients' => [
        '__file__' => Constants::$__DOC_ROOT__ . '/Config/Queries/GlobalDB/GET/clients.php',
        '{client_id:int}'  => [
            '__file__' => Constants::$__DOC_ROOT__ . '/Config/Queries/GlobalDB/GET/clients.php',
        ],
    ]
];
