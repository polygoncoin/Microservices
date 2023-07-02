<?php
return [
    'global' => [
        'links' => [
            '{link_id:int}'  => [
                '__file__' => __DOC_ROOT__ . '/Config/Queries/GlobalDB/PATCH/links.php',
            ],
            'approve'  => [
                '__file__' => __DOC_ROOT__ . '/Config/Queries/GlobalDB/PATCH/approve/links.php',
            ],
            'disable'  => [
                '__file__' => __DOC_ROOT__ . '/Config/Queries/GlobalDB/PATCH/disable/links.php',
            ],
            'enable'  => [
                '__file__' => __DOC_ROOT__ . '/Config/Queries/GlobalDB/PATCH/enable/links.php',
            ],
        ],
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
        'routes' => [
            '{route_id:int}'  => [
                '__file__' => __DOC_ROOT__ . '/Config/Queries/GlobalDB/PATCH/routes.php',
            ],
            'approve'  => [
                '__file__' => __DOC_ROOT__ . '/Config/Queries/GlobalDB/PATCH/approve/routes.php',
            ],
            'disable'  => [
                '__file__' => __DOC_ROOT__ . '/Config/Queries/GlobalDB/PATCH/disable/routes.php',
            ],
            'enable'  => [
                '__file__' => __DOC_ROOT__ . '/Config/Queries/GlobalDB/PATCH/enable/routes.php',
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
        'https' => [
            '{http_id:int}'  => [
                '__file__' => __DOC_ROOT__ . '/Config/Queries/GlobalDB/PATCH/https.php',
            ],
            'approve'  => [
                '__file__' => __DOC_ROOT__ . '/Config/Queries/GlobalDB/PATCH/approve/https.php',
            ],
            'disable'  => [
                '__file__' => __DOC_ROOT__ . '/Config/Queries/GlobalDB/PATCH/disable/https.php',
            ],
            'enable'  => [
                '__file__' => __DOC_ROOT__ . '/Config/Queries/GlobalDB/PATCH/enable/https.php',
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
    ],
    'client' => [
        '{table:string}' => [
            '{id:int}'  => [
                '__file__' => __DOC_ROOT__ . '/Config/Queries/ClientDB/PATCH/CRUD.php',
            ],
        ]
    ]        
];
