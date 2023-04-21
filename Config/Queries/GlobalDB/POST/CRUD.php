<?php
return [
    'l001_link_allowed_route' => [
        'query' => "INSERT INTO {$this->clientDB}.{$uriParams['table']} SET __SET__",
        'payload' => [
            //column => [payload|readOnlySession|insertIdParams|{custom} => key|{value}],
            'group_id' => ['payload' => 'group_id'],
            'client_id' => ['payload' => 'client_id'],
            'route_id' => ['payload' => 'route_id'],
            'http_id' => ['payload' => 'http_id'],
            'created_by' => ['readOnlySession' => 'id']
        ],
        'insertId' => 'm001_master_group:id',
        'validate' => [
            [
                'fn' => 'validateGroupId',
                'val' => ['payload' => 'group_id'],
                'errorMessage' => 'Invalid Group Id'
            ],
            [
                'fn' => 'validateClientId',
                'val' => ['payload' => 'client_id'],
                'errorMessage' => 'Invalid Client Id'
            ],
            [
                'fn' => 'validateRouteId',
                'val' => ['payload' => 'route_id'],
                'errorMessage' => 'Invalid Route Id'
            ],
            [
                'fn' => 'validateHttpId',
                'val' => ['payload' => 'http_id'],
                'errorMessage' => 'Invalid HTTP Id'
            ],
        ]
    ]
][$uriParams['table']];
