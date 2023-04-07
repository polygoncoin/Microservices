<?php
include __DOCROOT__ . ''

//Table Name.
$tableName = 'm005_master_db';

//Table columns configuration.
$tableColumnsConfig = [
    //Table columns with DataTypes.
    'columns' => [
        'id' => 'INT',
        'hostname' => 'VARCHAR',
        'database' => 'VARCHAR',
        'comment' => 'TEXT',
        'db_id' => 'INT',
    ],
    //Table columns alias.
    'columnAlias' => [
        'id' => 'primary',
        'hostname' => 'hostname',
        'database' => 'database',
        'comment' => 'comment',
    ],
    //Supported table column alias for respective HTTP verbs.
    'httpMethods' => [
        //GET column alias for output.
        'GET' => [
            'requiredColumns' => [
                'primary',
                'hostname',
                'database',
                'comment',
            ],
            'optionalColumns' => [
            ]
        ],
        //POST column alias for insert.
        'POST' => [
            'requiredColumns' => [//minimum required alias for insert
                'primary',
                'hostname',
                'database',
                'comment',
            ],
            'optionalColumns' => [//additional alias details that can also be added to the record.
            ]
        ],
        'PUT' => [
            'requiredColumns' => [//minimum required alias for update
                'primary',
                'hostname',
                'database',
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
                'hostname',
                'database',
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
