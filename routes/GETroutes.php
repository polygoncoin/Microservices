<?php
//routes support only integer dynamic variables.
//reserved keywords are 'required' & 'optional' & 'file'
$routes = [
    'crud' => [
        'file' => 'crud.php',
        '{int}'  => ['file' => 'crud.php']
    ]
];