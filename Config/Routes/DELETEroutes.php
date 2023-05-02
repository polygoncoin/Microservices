<?php
return [
    'global' => [
        '{table:string}' => [
            '{id:int}'  => [
                '__file__' => __DOC_ROOT__ . '/Config/Queries/GlobalDB/DELETE/CRUD.php',
            ],
        ]
    ],
    'client' => [
        '{table:string}' => [
            '{id:int}'  => [
                '__file__' => __DOC_ROOT__ . '/Config/Queries/ClientDB/DELETE/CRUD.php',
            ],
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
