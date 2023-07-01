<?php
return [
    'global' => [
        'links' => [
            '__file__' => __DOC_ROOT__ . '/Config/Queries/GlobalDB/GET/links.php',
            '{link_id:int}'  => [
                '__file__' => __DOC_ROOT__ . '/Config/Queries/GlobalDB/GET/links.php',
            ],
        ],
        'groups' => [
            '__file__' => __DOC_ROOT__ . '/Config/Queries/GlobalDB/GET/groups.php',
            '{group_id:int}'  => [
                '__file__' => __DOC_ROOT__ . '/Config/Queries/GlobalDB/GET/groups.php',
            ],
        ],
        'users' => [
            '__file__' => __DOC_ROOT__ . '/Config/Queries/GlobalDB/GET/users.php',
            '{user_id:int}'  => [
                '__file__' => __DOC_ROOT__ . '/Config/Queries/GlobalDB/GET/users.php',
            ],
        ],
        'routes' => [
            '__file__' => __DOC_ROOT__ . '/Config/Queries/GlobalDB/GET/routes.php',
            '{route_id:int}'  => [
                '__file__' => __DOC_ROOT__ . '/Config/Queries/GlobalDB/GET/routes.php',
            ],
        ],
        'connections' => [
            '__file__' => __DOC_ROOT__ . '/Config/Queries/GlobalDB/GET/connections.php',
            '{connection_id:int}'  => [
                '__file__' => __DOC_ROOT__ . '/Config/Queries/GlobalDB/GET/connections.php',
            ],
        ],
        'https' => [
            '__file__' => __DOC_ROOT__ . '/Config/Queries/GlobalDB/GET/https.php',
            '{http_id:int}'  => [
                '__file__' => __DOC_ROOT__ . '/Config/Queries/GlobalDB/GET/https.php',
            ],
        ],
        'clients' => [
            '__file__' => __DOC_ROOT__ . '/Config/Queries/GlobalDB/GET/clients.php',
            '{client_id:int}'  => [
                '__file__' => __DOC_ROOT__ . '/Config/Queries/GlobalDB/GET/clients.php',
            ],
        ],
    ],
    'client' => [
        '{table:string}' => [
            '__file__' => __DOC_ROOT__ . '/Config/Queries/ClientDB/GET/CRUD.php',
            '{id:int}'  => [
                '__file__' => __DOC_ROOT__ . '/Config/Queries/ClientDB/GET/CRUD.php',
            ],
        ]
    ],
    'thirdParty' => [
        'Google' => [
            '__file__' => ''
        ]
    ]
];
