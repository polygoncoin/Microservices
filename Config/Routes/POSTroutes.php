<?php
return [
    'global' => [
        'links' => [
            '__file__' => __DOC_ROOT__ . '/Config/Queries/GlobalDB/POST/links.php',
        ],
        'groups' => [
            '__file__' => __DOC_ROOT__ . '/Config/Queries/GlobalDB/POST/groups.php',
        ],
        'users' => [
            '__file__' => __DOC_ROOT__ . '/Config/Queries/GlobalDB/POST/users.php',
        ],
        'routes' => [
            '__file__' => __DOC_ROOT__ . '/Config/Queries/GlobalDB/POST/routes.php',
        ],
        'connections' => [
            '__file__' => __DOC_ROOT__ . '/Config/Queries/GlobalDB/POST/connections.php',
        ],
        'https' => [
            '__file__' => __DOC_ROOT__ . '/Config/Queries/GlobalDB/POST/https.php',
        ],
        'clients' => [
            '__file__' => __DOC_ROOT__ . '/Config/Queries/GlobalDB/POST/clients.php',
        ],
    ],
    'client' => [
        'registration' => [
            '__file__' => __DOC_ROOT__ . '/Config/Queries/ClientDB/POST/registration.php',
        ],
        '{table:string}' => [
            '__file__' => __DOC_ROOT__ . '/Config/Queries/ClientDB/POST/CRUD.php',
        ],
        'thirdParty' => [
            '{thirdParty:string}' => [
                '__file__' => ''
            ]
        ],
        'upload' => [
            '{module}:string' => [
                '__file__' => ''
            ]
        ]
    ]
];
