<?php
namespace Config\Routes\AdminGroup;

use App\Constants;
use App\HttpRequest;

return [
    'groups' => [
        '__file__' => Constants::$__DOC_ROOT__ . '/Config/Queries/GlobalDB/POST/groups.php',
    ],
    'users' => [
        '__file__' => Constants::$__DOC_ROOT__ . '/Config/Queries/GlobalDB/POST/users.php',
    ],
    'connections' => [
        '__file__' => Constants::$__DOC_ROOT__ . '/Config/Queries/GlobalDB/POST/connections.php',
    ],
    'clients' => [
        '__file__' => Constants::$__DOC_ROOT__ . '/Config/Queries/GlobalDB/POST/clients.php',
    ],
];
