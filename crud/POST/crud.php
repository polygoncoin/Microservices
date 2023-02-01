<?php
$query = 'INSERT INTO table SET __COLS__;';
$params = [];
if ($element !== 'int') {
    $config = [
        'uri' => '/crud',
        'payload' => [
            'required' => [],
            'optional' => []
        ],
        'session' = []
    ];
} else {
    $config = [
        'uri' => '/crud/{crudID}',
        'payload' => [//$payload
            'required' => [],
            'optional' => []
        ],
        'session' = []
    ];
}
