<?php
return [
    'crud' => [
        '{table:string}' => [
            '__file__' => __DOC_ROOT__ . '/app/includes/' . $method . '/crud.php',
            '{id:int}'  => [
                '__file__' => __DOC_ROOT__ . '/app/includes/' . $method . '/crud.php',
            ],
        ]
    ],
    '3p' => [
        '{reference:string}' => [
            '__file__' => __DOC_ROOT__ . '/app/includes/' . $method . '/crud.php',
            '{id:int}'  => [
                '__file__' => __DOC_ROOT__ . '/app/includes/' . $method . '/crud.php',
            ],
        ],
        '{reference:int}' => [
            'add'  => [
                '__file__' => __DOC_ROOT__ . '/app/includes/' . $method . '/crud.php',
            ],
        ]
    ],
];
