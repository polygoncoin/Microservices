<?php
return [
    'm006_master_client' => [
        'queries' => [
            [
                'query' => "INSERT INTO {$this->clientDB}.m006_master_client SET name = ?;",
                'payload' => [
                    'name'
                ]
            ]
        ],
        'validate' => [
            /*[
                'fn' => 'validateFunction',
                'val' => 'username',
                'errorMessage' => ''
            ],*/
        ]
    ]
][$uriParams['table']];
