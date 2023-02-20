<?php
//routes support only integer dynamic variables.
//reserved keywords are 'required' & 'optional' & 'file'
$routes = [
    '{table:string}' => [
        '__file__' => __DOC_ROOT__ . '/crud/' . $method . '/crud.php',
        '{id:int}'  => [
            '__file__' => __DOC_ROOT__ . '/crud/' . $method . '/crud.php',
        ],
        '{orderBy:string}|ASC,DESC'  => [
            '__file__' => __DOC_ROOT__ . '/crud/' . $method . '/crud.php',
            '{AscDes:string}'  => [
                '__file__' => __DOC_ROOT__ . '/crud/' . $method . '/crud.php',
            ],
        ],
    ]
];