<?php
return [
    'queries' => [
        [
            'query' => "INSERT INTO {$this->clientDB}.{$uriParams['table']} SET " . implode(', ',array_map(function ($value) { return '`' . str_replace('`','',$value) . '` = ?';}, array_keys($_POST))) . ';',
            'payload' => [
                $_POST['123'],
            ],
            'insertId' => 'table1Id',
            'subQuery' => 
            [
                [
                    'query' => "INSERT INTO {$this->clientDB}.{$uriParams['table']} SET  ;",
                    'payload' => [
                        $_POST,
                        $tableId
                    ],
                    'insertId' => 'table2Id',
                ],
            ]
        ],
    ],
    'validate' => [
        [
            'fn' => 'validateRequired',
            'payloadKey' => 'username',
            'errorMessage' => ''
        ],
    ]
];


