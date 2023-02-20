<?php
// Load Payload
parse_str(file_get_contents('php://input'), $payload);

$columnsConfig = [
    'table0' => [
        'payload' => [
            'required' => [],
            'optional' => []
        ]
    ],
]
if (!isset($columnsConfig[$uriParams['table']])) {

}
$query = "INSERT INTO `{$uriParams['table']}` SET __COLS__;";
$params = [];
