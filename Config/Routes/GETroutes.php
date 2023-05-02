<?php
return [
    'global' => [
        '{table:string}' => [
            '__file__' => __DOC_ROOT__ . '/Config/Queries/GlobalDB/GET/CRUD.php',
            '{id:int}'  => [
                '__file__' => __DOC_ROOT__ . '/Config/Queries/GlobalDB/GET/CRUD.php',
            ],
        ],
        'getGroupInfo' => [
            '__file__' => __DOC_ROOT__ . '/Config/Queries/GlobalDB/GET/GroupInfo.php',
        ]
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
