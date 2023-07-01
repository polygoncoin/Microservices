<?php
return [
    'global' => [
        'links' => [
            '{link_id:int}'  => [
                '__file__' => __DOC_ROOT__ . '/Config/Queries/GlobalDB/PUT/links.php',
            ],
        ],
        'groups' => [
            '{group_id:int}'  => [
                '__file__' => __DOC_ROOT__ . '/Config/Queries/GlobalDB/PUT/groups.php',
            ],
        ],
        'users' => [
            '{user_id:int}'  => [
                '__file__' => __DOC_ROOT__ . '/Config/Queries/GlobalDB/PUT/users.php',
            ],
        ],
        'routes' => [
            '{route_id:int}'  => [
                '__file__' => __DOC_ROOT__ . '/Config/Queries/GlobalDB/PUT/routes.php',
            ],
        ],
        'connections' => [
            '{connection_id:int}'  => [
                '__file__' => __DOC_ROOT__ . '/Config/Queries/GlobalDB/PUT/connections.php',
            ],
        ],
        'https' => [
            '{http_id:int}'  => [
                '__file__' => __DOC_ROOT__ . '/Config/Queries/GlobalDB/PUT/https.php',
            ],
        ],
        'clients' => [
            '{client_id:int}'  => [
                '__file__' => __DOC_ROOT__ . '/Config/Queries/GlobalDB/PUT/clients.php',
            ],
        ],
    ],
    'client' => [
        '{table:string}' => [
            '{id:int}'  => [
                '__file__' => __DOC_ROOT__ . '/Config/Queries/ClientDB/PUT/CRUD.php',
            ],
        ]
    ]
];
