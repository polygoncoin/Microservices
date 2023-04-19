<?php
return [
    'm001_master_group' => [
        'queries' => [
            [
                'query' => "INSERT INTO {$this->clientDB}.{$uriParams['table']} SET " . implode(', ',array_map(function ($value) { return '`' . str_replace('`','',$value) . '` = ?';}, array_keys($_POST))) . ';',
                'payload' => array_merge(
                    array_values($_POST),
                    []
                )
            ]
        ],
        'validate' => [
            [
                'fn' => 'validateRequired',
                'payloadKey' => 'username',
                'errorMessage' => ''
            ],
        ]
    ]
][$uriParams['table']];
