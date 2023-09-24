<?php
return [
    'groups' => [
        '{group_id:int}'  => [
            '__file__' => __DOC_ROOT__ . '/Config/Queries/GlobalDB/PATCH/groups.php',
        ],
        'approve'  => [
            '__file__' => __DOC_ROOT__ . '/Config/Queries/GlobalDB/PATCH/approve/groups.php',
        ],
        'disable'  => [
            '__file__' => __DOC_ROOT__ . '/Config/Queries/GlobalDB/PATCH/disable/groups.php',
        ],
        'enable'  => [
            '__file__' => __DOC_ROOT__ . '/Config/Queries/GlobalDB/PATCH/enable/groups.php',
        ],
    ],
    'users' => [
        '{user_id:int}'  => [
            '__file__' => __DOC_ROOT__ . '/Config/Queries/GlobalDB/PATCH/users.php',
        ],
        'approve'  => [
            '__file__' => __DOC_ROOT__ . '/Config/Queries/GlobalDB/PATCH/approve/users.php',
        ],
        'disable'  => [
            '__file__' => __DOC_ROOT__ . '/Config/Queries/GlobalDB/PATCH/disable/users.php',
        ],
        'enable'  => [
            '__file__' => __DOC_ROOT__ . '/Config/Queries/GlobalDB/PATCH/enable/users.php',
        ],
    ],
    'connections' => [
        '{connection_id:int}'  => [
            '__file__' => __DOC_ROOT__ . '/Config/Queries/GlobalDB/PATCH/connections.php',
        ],
        'approve'  => [
            '__file__' => __DOC_ROOT__ . '/Config/Queries/GlobalDB/PATCH/approve/connections.php',
        ],
        'disable'  => [
            '__file__' => __DOC_ROOT__ . '/Config/Queries/GlobalDB/PATCH/disable/connections.php',
        ],
        'enable'  => [
            '__file__' => __DOC_ROOT__ . '/Config/Queries/GlobalDB/PATCH/enable/connections.php',
        ],
    ],
    'clients' => [
        '{client_id:int}'  => [
            '__file__' => __DOC_ROOT__ . '/Config/Queries/GlobalDB/PATCH/clients.php',
        ],
        'approve'  => [
            '__file__' => __DOC_ROOT__ . '/Config/Queries/GlobalDB/PATCH/approve/clients.php',
        ],
        'disable'  => [
            '__file__' => __DOC_ROOT__ . '/Config/Queries/GlobalDB/PATCH/disable/clients.php',
        ],
        'enable'  => [
            '__file__' => __DOC_ROOT__ . '/Config/Queries/GlobalDB/PATCH/enable/clients.php',
        ],
    ],
];
