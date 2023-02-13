<?php
$uriConfig = [
    'uri' => '/crud/{tableName}',
];
$columnsConfig = [
    'table0' => [
        'payload' => [
            'required' => [],
            'optional' => []
        ]
    ],
    'table1' => [
        'payload' => [
            'required' => [],
            'optional' => []
        ]
    ]
]
if (!in_array($uriParams[':tableName'], array_keys($config))) {

}
$query = "INSERT INTO {$uriParams[':tableName']} SET __COLS__;";
$params = [];
