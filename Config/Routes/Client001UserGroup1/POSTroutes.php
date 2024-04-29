<?php
return [
    'registration' => [
        '__file__' => __DOC_ROOT__ . '/Config/Queries/ClientDB/POST/Registration.php',
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
];
