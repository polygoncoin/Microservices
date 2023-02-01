<?php
$query = 'UPDATE `table` SET __COLS__ WHERE id = :id';
$params = [];
if ($element !== 'int') {
    $config = [
        'uri' => '/crud',
        'uriParams' => [],
        'payload' => [
            'required' => [],
            'optional' => []
        ],
        'session' = []
    ];
} else {
    $config = [
        'uri' => '/crud/{crud_id}',
        'uriParams' => ['crud_id' => $uriParams[1]],
        'payload' => [//$payload
            'required' => [],
            'optional' => []
        ],
        'session' = []
    ];
}
