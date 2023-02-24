<?php
$routes = [
    'crud' => [
        '{table:string}' => [
            '{id:int}'  => [
                '__file__' => __DOC_ROOT__ . '/apiDetails/include/' . $method . '/crud.php',
            ],
        ]
    ],
];
