<?php
return [
    'queries' => [
        // Array of Queries
        [
            'query' => "INSERT INTO {$this->clientDB}.{$uriParams['table']} SET " . implode(', ',array_map(function ($value) { return '`' . str_replace('`','',$value) . '` = ?';}, array_keys($_POST))) . ';',
            'payload' => [
                $_POST['123'],
            ],
            'insertId' => ':table1Id',
            // Array of Sub Queries
            'subQuery' => 
            [
                [
                    'query' => "INSERT INTO {$this->clientDB}.{$uriParams['table']} SET  ;",
                    'payload' => [
                        $_POST,
                        ':table1Id'
                    ],
                    'insertId' => 'table2Id',
                    // The sub queries can go to n level
                    'subQuery' => 
                    []
                ],
            ]
        ],
        [
            'query' => "INSERT INTO {$this->clientDB}.{$uriParams['table']} SET " . implode(', ',array_map(function ($value) { return '`' . str_replace('`','',$value) . '` = ?';}, array_keys($_POST))) . ';',
            'payload' => [
                $_POST['123'],
            ],
            'insertId' => 'table1Id',
            // Array of Sub Queries
            'subQuery' => 
            [
                [
                    'query' => "INSERT INTO {$this->clientDB}.{$uriParams['table']} SET  ;",
                    'payload' => [
                        $_POST,
                        $tableId
                    ],
                    'insertId' => 'table2Id',
                    // The sub queries can go to n level
                    'subQuery' => 
                    []
                ],
            ]
        ]
    ],
    // Validate is one time validation for all the data.
    // One can configure the validation function in  App\Validation\Validate.php
    // Each validation function should return boolean true/false
    'validate' => [
        [
            'fn' => 'validateRequired',
            'dataKey' => 'username',
            'errorMessage' => 'Error Message'
        ],
        [
            'fn' => 'validateRequired',
            'val' => $_POST['username'],
            'errorMessage' => 'Error Message'
        ],
    ]
];


