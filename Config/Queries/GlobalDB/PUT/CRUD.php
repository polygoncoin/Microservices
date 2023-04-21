<?php
return [
    'm001_master_group' => [
        'queries' => [
            [
                'query' => "UPDATE {$this->clientDB}.m001_master_group SET __SET__ WHERE __WHERE__;",
                'payload' => [
                    'group_id' => ['payload' => 'group_id'],
                    'client_id' => ['payload' => 'client_id'],
                    'route_id' => ['payload' => 'route_id'],
                    'http_id' => ['payload' => 'http_id'],
                    'updated_by' => ['readOnlySession' => 'id']
                ],
                'where' => [
                    'id' => ['payload' => 'id'],
                ]
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
