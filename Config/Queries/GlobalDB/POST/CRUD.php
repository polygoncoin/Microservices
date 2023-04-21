<?php
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
                    'created_by' => ['readOnlySession' => 'id']
                ],
                'insertId' => 'm001_master_group:id',
                'subQuery' => [
                    'queries' => [
                        [
                            'query' => "INSERT INTO {$this->clientDB}.{$uriParams['table']} SET __SET__;",
                            'payload' => [
                                'group_id' => ['payload' => 'group_id'],
                                'client_id' => ['payload' => 'client_id'],
                                'route_id' => ['payload' => 'route_id'],
                                'http_id' => ['payload' => 'http_id'],
                                'created_by' => ['readOnlySession' => 'id'],
                                'group_ids' => ['insertIdParams' => 'm001_master_group:id']
                            ],
                            'insertId' => 'm001_master_group:id',
                            'subQuery' => [
                                'queries' => [
                                    [
                                        'query' => "INSERT INTO {$this->clientDB}.{$uriParams['table']} SET __SET__;",
                                        'payload' => [
                                            'group_id' => ['payload' => 'group_id'],
                                            'client_id' => ['payload' => 'client_id'],
                                            'route_id' => ['payload' => 'route_id'],
                                            'http_id' => ['payload' => 'http_id'],
                                            'created_by' => ['readOnlySession' => 'id']
                                        ],
                                        'insertId' => 'm001_master_group:id',
                                    ]
                                ]
                            ]
                        ]
                    ]
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
    ]
][$uriParams['table']];
