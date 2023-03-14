<?php
$routes = [
    'crud' => [
        '{table:string}' => [
            '__file__' => __DOC_ROOT__ . '/app/includes/' . $method . '/crud.php',
            '{id:int}'  => [
                '__file__' => __DOC_ROOT__ . '/app/includes/' . $method . '/crud.php',
            ]
        ]
    ],
];