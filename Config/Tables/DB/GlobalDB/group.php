<?php
include __DOCROOT__ . ''

//Table Name
$tableName = 'm001_master_group';

$tableColumnsConfig = [
    'columns' => [
        'id' => 'INT',
        'name' => 'VARCHAR',
        'comment' => 'TEXT',
        'allowed_ips' => 'TEXT',
        'client_ids' => 'JSON'
    ],
    'columnAlias' => [
        'id' => 'primary',
        'name' => 'name',
        'comment' => 'comment',
        'allowed_ips' => 'allowedIps',
        'client_ids' => 'clientIds'
    ],
    'httpMethods' => [
        'GET' => [
            'requiredColumns' => [//alias for output
                'primary',
                'name',
                'comment',
                'allowedIps',
                'clientIds'
            ],
            'optionalColumns' => [//additional alias
            ]
        ],
        'POST' => [
            'requiredColumns' => [//minimum required alias for insert
                'primary',
                'name',
                'comment',
                'allowedIps',
                'clientIds'
            ],
            'optionalColumns' => [//additional alias details that can also be added to the record.
            ]
        ],
        'PUT' => [
            'requiredColumns' => [//minimum required alias for update
                'primary',
                'name',
                'comment',
                'allowedIps',
                'clientIds'
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
                'name',
                'comment',
                'allowedIps',
                'clientIds'
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
