<?php
// Load Payload
parse_str(file_get_contents('php://input'), $payload);

//routes support only integer dynamic variables.
//reserved keywords are 'required' & 'optional'
$routes = [
    'crud' => [
        'required' => [

        ],
        'optional' => [

        ]
    ]
];