<?php
include __DOCROOT__ . ''

//Table columns configuration.
$tableColumnsConfig['client'] = [
    //Table Name.
    'tableName' => 'm002_master_client',
    //Table columns with DataTypes for its values validation.
    'columns' => [
        'id' => 'INT',
        'name' => 'VARCHAR',
        'comment' => 'TEXT',
        'client_id' => 'INT',
        'added_by' => 'INT',
        'added_datetime' => 'DATETIME',
        'updated_by' => 'INT',
        'updated_datetime' => 'DATETIME',
        'is_approved' => 'BIT',
        'approved_by' => 'INT',
        'approved_datetime' => 'DATETIME',
        'is_disabled' => 'BIT',
        'disabled_by' => 'INT',
        'disabled_datetime' => 'DATETIME',
        'is_deleted' => 'BIT',
        'deleted_by' => 'INT',
        'deleted_datetime' => 'DATETIME',
    ],
    //Table columns alias.
    'columnAlias' => [
        'id' => 'primary',
        'name' => 'name',
        'comment' => 'comment',
        'client_id' => 'clientId',
        'added_by' => 'addedBy',
        'added_datetime' => 'addedDateTime',
        'updated_by' => 'updatedBy',
        'updated_datetime' => 'updatedDateTime',
        'is_approved' => 'approved',
        'approved_by' => 'approvedBy',
        'approved_datetime' => 'approvedDateTime',
        'is_disabled' => 'disabled',
        'disabled_by' => 'disabledBy',
        'disabled_datetime' => 'disabledDateTime',
        'is_deleted' => 'deleted',
        'deleted_by' => 'deletedBy',
        'deleted_datetime' => 'deletedDateTime',
    ],
    //Supported table column alias for respective HTTP verbs.
    'httpMethods' => [
        //GET column alias for output.
        'GET' => [
            //Column alias that will be returned for sure.
            'requiredAlias' => [
                'primary',
                'name',
                'comment',
            ],
            //Column alias that will be returned with required alias with GET request with '&return=all'.
            'optionalAlias' => [
                'disabled'
            ]
        ],
        //POST column alias for insert.
        'POST' => [
            //Required column alias.
            'requiredAlias' => [
                'name',
                'comment',
            ],
            //Optional column alias.
            'optionalAlias' => [
            ],
            //Default values of column alias used while adding record.
            'defaultAlias' => [
                'clientId' => $_SESSION['clientId'],
                'addedBy' => $_SESSION['userId'],
                'addedDateTime' => date('Y-m-d H:i:s'),
                'updatedBy' => NULL,
                'updatedDateTime' => NULL,
                'approved' => 0,
                'approvedBy' => NULL,
                'approvedDateTime' => NULL,
                'disabled' => 0,
                'disabledBy' => NULL,
                'disabledDateTime' => NULL,
                'deleted' => 0,
                'deletedBy' => NULL,
                'deletedDateTime' => NULL,
            ]
        ],
        //PUT column alias for update.
        'PUT' => [
            //Minimum required column alias for update.
            'requiredAlias' => [
                'name',
                'comment',
            ],
            //Additional column alias that can also be updated.
            'optionalAlias' => [
            ],
            //Default values of column alias used while adding record.
            'defaultAlias' => [
                'updatedBy' => $_SESSION['userId'],
                'updatedDateTime' => date('Y-m-d H:i:s'),
            ],
            //Supported column alias for updating the record. Minimum one among this is required.
            'supportedKeys' => ['primary']
        ],
        //PATCH column alias for update.
        'PATCH' => [
            //Minimum required column alias for update.
            'requiredAlias' => [
            ],
            //Additional column alias that can also be updated.
            'optionalAlias' => [
                'name',
                'comment',
            ],
            //Default values of column alias used while adding record.
            'defaultAlias' => [
                'updatedBy' => $_SESSION['userId'],
                'updatedDateTime' => date('Y-m-d H:i:s'),
            ],
            //Supported column alias for updating the record. Minimum one among this is required.
            'supportedKeys' => ['primary']
        ],
        //DELETE column alias for update.
        'DELETE' => [
            //Minimum required column alias for delete operation.
            'requiredAlias' => [
            ],
            'optionalAlias' => [
            ],
            //Default values of column alias used while adding record.
            'defaultAlias' => [
                'deletedBy' => $_SESSION['userId'],
                'deletedDateTime' => date('Y-m-d H:i:s'),
            ],
            //Supported column alias for updating the record. Minimum one among this is required.
            'supportedKeys' => ['primary']
        ],
    ]
];
