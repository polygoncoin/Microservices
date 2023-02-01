<?php
//$uriParams,
//$payload
$queries = [
    ['SELECT * FROM table __WHERE__;', []],
    'key1' => ['SELECT * FROM table __WHERE__;', []],
    'key2' => ['SELECT * FROM table __WHERE__;', []]
];
if ($element !== 'int') {
    $config = [
        'uri' => '/crud',
        'uriParams' => [],
        'session' = []//if set these will be required always.
    ];
} else {
    $config = [
        'uri' => '/crud/{crud_id}',
        'uriParams' => [':crud_id' => $uriParams[1]],
        'session' = []
    ];
}
