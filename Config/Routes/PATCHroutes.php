<?php
return [
    'global' => [
        'links' => [
            '{link_id:int}'  => [
                '__file__' => __DOC_ROOT__ . '/Config/Queries/GlobalDB/PATCH/links.php',
            ],
        ],
        'groups' => [
            '{group_id:int}'  => [
                '__file__' => __DOC_ROOT__ . '/Config/Queries/GlobalDB/PATCH/groups.php',
            ],
        ],
        'users' => [
            '{user_id:int}'  => [
                '__file__' => __DOC_ROOT__ . '/Config/Queries/GlobalDB/PATCH/users.php',
            ],
        ],
        'routes' => [
            '{route_id:int}'  => [
                '__file__' => __DOC_ROOT__ . '/Config/Queries/GlobalDB/PATCH/routes.php',
            ],
        ],
        'connections' => [
            '{connection_id:int}'  => [
                '__file__' => __DOC_ROOT__ . '/Config/Queries/GlobalDB/PATCH/connections.php',
            ],
        ],
        'https' => [
            '{http_id:int}'  => [
                '__file__' => __DOC_ROOT__ . '/Config/Queries/GlobalDB/PATCH/https.php',
            ],
        ],
        'clients' => [
            '{client_id:int}'  => [
                '__file__' => __DOC_ROOT__ . '/Config/Queries/GlobalDB/PATCH/clients.php',
            ],
        ],
    ],
    'client' => [
        '{table:string}' => [
            '{id:int}'  => [
                '__file__' => __DOC_ROOT__ . '/Config/Queries/ClientDB/PATCH/CRUD.php',
            ],
        ]
    ]        
];
