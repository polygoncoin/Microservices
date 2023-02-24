<?php
$routes = [
    'crud' => [
        '{table:string}' => [
            '__file__' => __DOC_ROOT__ . '/apiDetails/include/' . $method . '/crud.php',
            '{id:int}'  => [
                '__file__' => __DOC_ROOT__ . '/apiDetails/include/' . $method . '/crud.php',
            ],
            '{orderBy:string}|ASC,DESC'  => [
                '__file__' => __DOC_ROOT__ . '/apiDetails/include/' . $method . '/crud.php',
                '{AscDes:string}'  => [
                    '__file__' => __DOC_ROOT__ . '/apiDetails/include/' . $method . '/crud.php',
                ],
            ],
        ]
    ],
];