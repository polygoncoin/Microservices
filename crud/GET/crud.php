<?php
//$uriParams,
//$payload
$queries = [
    'key1' => ['SELECT * FROM table __WHERE__;'],
    'key2' => ['SELECT * FROM table __WHERE__;']
];
if ($pos !== 0) {
    $config = [
        'uri' => '/crud',
        'payload' => [
            'required' => [],
            'optional' => []
        ]
    ];
} else {
    $config = [
        'uri' => '/crud/{crud_id}',
        'payload' => [
            'required' => [],
            'optional' => []
        ]
    ];
}
