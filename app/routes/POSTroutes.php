<?php
$routes = [
    'crud' => [
        '{table:string}' => [
            '__file__' => __DOC_ROOT__ . '/app/include/' . $method . '/crud.php',
        ]
    ],
];
