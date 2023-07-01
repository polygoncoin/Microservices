<?php
return [
    'global' => [
        'links' => [
            '{link_id:int}'  => [
                '__file__' => __DOC_ROOT__ . '/Config/Queries/GlobalDB/DELETE/links.php',
            ],
        ],
        'groups' => [
            '{group_id:int}'  => [
                '__file__' => __DOC_ROOT__ . '/Config/Queries/GlobalDB/DELETE/groups.php',
            ],
        ],
        'users' => [
            '{user_id:int}'  => [
                '__file__' => __DOC_ROOT__ . '/Config/Queries/GlobalDB/DELETE/users.php',
            ],
        ],
        'routes' => [
            '{route_id:int}'  => [
                '__file__' => __DOC_ROOT__ . '/Config/Queries/GlobalDB/DELETE/routes.php',
            ],
        ],
        'connections' => [
            '{connection_id:int}'  => [
                '__file__' => __DOC_ROOT__ . '/Config/Queries/GlobalDB/DELETE/connections.php',
            ],
        ],
        'https' => [
            '{http_id:int}'  => [
                '__file__' => __DOC_ROOT__ . '/Config/Queries/GlobalDB/DELETE/https.php',
            ],
        ],
        'clients' => [
            '{client_id:int}'  => [
                '__file__' => __DOC_ROOT__ . '/Config/Queries/GlobalDB/DELETE/clients.php',
            ],
        ],
    ],
    'client' => [
        '{table:string}' => [
            '{id:int}'  => [
                '__file__' => __DOC_ROOT__ . '/Config/Queries/ClientDB/DELETE/CRUD.php',
            ],
        ]
    ]
];
