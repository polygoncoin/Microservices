<?php
return [
    'query' => "UPDATE `{$this->globalDB}`.`{$this->execPhpFunc(getenv('clients'))}` SET __SET__ WHERE __WHERE__",
    'payload' => [
        //column => [payload|readOnlySession|uriParams|insertIdParams|{custom}, key|{value}],
        'is_disabled' => ['custom', 'Yes'],
        'updated_by' => ['readOnlySession', 'user_id'],
        'updated_on' => ['custom', date('Y-m-d H:i:s')]
    ],
    'where' => [
        'is_disabled' => ['custom', 'No'],
        'is_deleted' => ['custom', 'No'],
        'client_id' => ['payload', 'client_id']
    ],
    'validate' => [
		[
			'fn' => 'primaryKeyExist',
			'fnArgs' => [
                'table' => ['custom', getenv('clients')],
                'primary' => ['custom', 'client_id'],
                'id' => ['payload', 'client_id']
            ],
			'errorMessage' => 'Invalid Client Id'
		],
		[
			'fn' => 'checkColumnValueExist',
			'fnArgs' => [
                'table' => ['custom', getenv('clients')],
                'column' => ['custom', 'is_deleted'],
                'columnValue' => ['custom', 'No'],
                'primary' => ['custom', 'client_id'],
                'id' => ['payload', 'client_id'],
            ],
			'errorMessage' => 'Record is deleted'
		],
		[
			'fn' => 'checkColumnValueExist',
			'fnArgs' => [
                'table' => ['custom', getenv('clients')],
                'column' => ['custom', 'is_disabled'],
                'columnValue' => ['custom', 'No'],
                'primary' => ['custom', 'client_id'],
                'id' => ['payload', 'client_id'],
            ],
			'errorMessage' => 'Record is already disabled'
		]
	]
];
