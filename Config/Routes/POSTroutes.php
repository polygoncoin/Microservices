<?php
return [
    'global' => [
        '{table:string}' => [
            '__file__' => __DOC_ROOT__ . '/Config/Queries/GlobalDB/POST/CRUD.php',
        ]
    ],
    'client' => [
        '{table:string}' => [
            '__file__' => __DOC_ROOT__ . '/Config/Queries/ClientDB/POST/CRUD.php',
        ]
    ],
    'thirdParty' => [
        'Google' => [
            '__file__' => ''
        ]
    ],
    'cache' => [
        '{clientId:int}' => [
            '__file__' => ''
        ]
    ],
    'migrate' => [
        '{connectionId:int}' => [
            '__file__' => ''
        ]
    ]
];
