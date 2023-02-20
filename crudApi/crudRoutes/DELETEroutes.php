<?php
$routes = [
    '{table:string}' => [
        '{id:int}'  => [
            '__file__' => __DOC_ROOT__ . '/crudAPI/crudConfig/' . $method . '/crud.php',
        ],
    ]
];
