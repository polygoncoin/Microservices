<?php
include __DOCROOT__ . ''
$tableColumnsConfig = [
    'columns' => [
        'id' => 'INT',
    ],
    'columnAlias' => [
        'id' => 'primary'
    ],
    'httpMethods' => [
        'GET' => [
            'requiredColumns' = [//alias for output
                'primary',
            ],
            'optionalColumns' = [//additional alias
                '',
            ]
        ],
        'POST' => [
            'requiredColumns' = [//minimum required alias for insert
            ],
            'optionalColumns' = [//additional alias details that can also be added to the record.
                'primary'
            ]
        ],
        'PUT' => [
            'requiredColumns' = [//minimum required alias for update
            ],
            'optionalColumns' = [//additional alias details that can also be updated to the record.
                'primary'
            ],
            'supportedKeys' => ['primary']//supported keys for update. Any one among this is required
        ],
        'PATCH' => [
            'requiredColumns' = [//minimum required alias for update
            ],
            'optionalColumns' = [//additional alias details that can also be updated to the record.
                'primary'
            ],
            'supportedKeys' => ['primary']//supported keys for update. Any one among this is required
        ],
        'DELETE' => [
            'requiredColumns' = [
            ],
            'optionalColumns' = [
                'primary'
            ],
            'supportedKeys' => ['primary']//supported keys for soft delete. Any one among this is required
        ],
    ]
];
