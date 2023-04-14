<?php
include __DOCROOT__ . ''

//Table Name
$tableName = 'l001_link_allowed_route';

$tableColumnsConfig = [
    'columns' => [
        'id' => 'INT',
        'group_id' => 'INT',
        'client_id' => 'INT',
        'module_id' => 'INT',
        'route_id' => 'INT',
        'http_id' => 'INT'
    ],
    'columnAlias' => [
        'id' => 'primary',
        'group_id' => 'groupId',
        'client_id' => 'clientId',
        'module_id' => 'moduleId',
        'route_id' => 'routeId',
        'http_id' => 'httpId'
    ],
    'httpMethods' => [
        'GET' => [
            'requiredColumns' => [//alias for output
                'primary',
                'groupId',
                'clientId',
                'moduleId',
                'httpId',
            ],
            'optionalColumns' => [//additional alias
            ]
        ],
        'POST' => [
            'requiredColumns' => [//minimum required alias for insert
                'primary',
                'groupId',
                'clientId',
                'moduleId',
                'httpId',
            ],
            'optionalColumns' => [//additional alias details that can also be added to the record.
            ]
        ],
        'PUT' => [
            'requiredColumns' => [//minimum required alias for update
                'primary',
                'groupId',
                'clientId',
                'moduleId',
                'httpId',
            ],
            'optionalColumns' => [//additional alias details that can also be updated to the record.
            ],
            'supportedKeys' => ['primary']//supported keys for update. Any one among this is required
        ],
        'PATCH' => [
            'requiredColumns' => [//minimum required alias for update
                'primary',
            ],
            'optionalColumns' => [//additional alias details that can also be updated to the record.
                'groupId',
                'clientId',
                'moduleId',
                'httpId',
            ],
            'supportedKeys' => ['primary']//supported keys for update. Any one among this is required
        ],
        'DELETE' => [
            'requiredColumns' => [
                'primary'
            ],
            'optionalColumns' => [
            ],
            'supportedKeys' => ['primary']//supported keys for soft delete. Any one among this is required
        ],
    ]
];
