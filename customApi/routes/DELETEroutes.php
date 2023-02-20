<?php
$routes = [
    '{table:string}' => [
        '{id:int}'  => [
            '__file__' => __DOC_ROOT__ . '/crud/' . $method . '/crud.php',
        ],
    ]
];
