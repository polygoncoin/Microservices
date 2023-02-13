<?php
//routes support only integer dynamic variables.
//reserved keywords are 'required' & 'optional' & 'file'
$routes = [
    'crud' => [
        '__file__' => 'crud.php',
        '{int}'  => ['file' => 'crud.php']
    ]
];