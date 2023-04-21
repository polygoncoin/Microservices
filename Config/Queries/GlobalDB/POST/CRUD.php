<?php



 |
| m001_master_group       |
| m002_master_user        |
| m003_master_route       |
| m004_master_connection  |
| m005_master_http        |
| m006_master_client
return [
    'l001_link_allowed_route' => [
        'queries' => [
            [
                'query' => "INSERT INTO {$this->clientDB}.{$uriParams['table']} SET __SET__;",
                'payload' => [
                    'group_id' => ['payload' => 'group_id'],
                    'client_id' => ['payload' => 'client_id'],
                    'route_id' => ['payload' => 'route_id'],
                    'http_id' => ['payload' => 'http_id'],
                    'created_by' => []
                ],
                'where' => [
                    'id' => ['payload' => 'id'],
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
    ],
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
    ],
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
    ],
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
    ],
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
    ],
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
    ],
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
    ],
][$uriParams['table']];
