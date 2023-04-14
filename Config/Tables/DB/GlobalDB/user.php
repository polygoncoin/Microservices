<?php
include __DOCROOT__ . ''

//Table Name
$tableName = 'm007_master_user';

$tableColumnsConfig = [
    'columns' => [
        'id' => 'INT',
        'username' => 'VARCHAR',
        'password_hash' => 'VARCHAR',
        'group_ids' => 'JSON',
        'allowed_ips' => 'TEXT',
        'comment' => 'TEXT',
    ],
    'columnAlias' => [
        'id' => 'primary',
        'username' => 'username',
        'password_hash' => 'password',
        'group_ids' => 'groupIds',
        'allowed_ips' => 'allowedIps',
        'comment' => 'comment',
    ],
    'httpMethods' => [
        'GET' => [
            'requiredColumns' => [//alias for output
                'primary',
                'username',
                'groupIds',
                'allowedIps',
                'comment',
            ],
            'optionalColumns' => [//additional alias
            ]
        ],
        'POST' => [
            'requiredColumns' => [//minimum required alias for insert
                'primary',
                'username',
                'groupIds',
                'allowedIps',
                'comment',
            ],
            'optionalColumns' => [//additional alias details that can also be added to the record.
            ]
        ],
        'PUT' => [
            'requiredColumns' => [//minimum required alias for update
                'primary',
                'username',
                'groupIds',
                'allowedIps',
                'comment',
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
                'username',
                'groupIds',
                'allowedIps',
                'comment',
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
