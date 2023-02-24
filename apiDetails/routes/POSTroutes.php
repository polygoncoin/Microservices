<?php
$routes = [
    'crud' => [
        '{table:string}' => [
            '__file__' => __DOC_ROOT__ . '/apiDetails/include/' . $method . '/crud.php',
        ]
    ],
];
