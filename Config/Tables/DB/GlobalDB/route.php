<?php
include __DOCROOT__ . ''

//Table Name
$tableName = 'm004_master_route';

$tableColumnsConfig = [
    'columns' => [
        'id' => 'INT',
        'name' => 'VARCHAR',
        'comment' => 'TEXT',
        'db_id' => 'INT',
    ],
    'columnAlias' => [
        'id' => 'primary',
        'name' => 'name',
        'comment' => 'comment',
        'db_id' => 'databaseId',
    ],
    'httpMethods' => [
        'GET' => [
            'requiredColumns' => [//alias for output
                'primary',
                'name',
                'comment',
                'databaseId',
            ],
            'optionalColumns' => [//additional alias
            ]
        ],
        'POST' => [
            'requiredColumns' => [//minimum required alias for insert
                'primary',
                'name',
                'comment',
                'databaseId',
            ],
            'optionalColumns' => [//additional alias details that can also be added to the record.
            ]
        ],
        'PUT' => [
            'requiredColumns' => [//minimum required alias for update
                'primary',
                'name',
                'comment',
                'databaseId',
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
                'databaseId',
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
