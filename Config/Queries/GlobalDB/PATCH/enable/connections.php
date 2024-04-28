<?php
namespace Config\Queries\GlobalDB\PATCH\enable;

use App\HttpRequest;

return [
    'query' => "UPDATE `{$this->globalDB}`.`{$this->execPhpFunc(getenv('connections'))}` SET __SET__ WHERE __WHERE__",
    'payload' => [
        //column => [payload|readOnlySession|uriParams|insertIdParams|{custom}, key|{value}],
        'is_disabled' => ['custom', 'No'],
        'updated_by' => ['readOnlySession', 'user_id'],
        'updated_on' => ['custom', date('Y-m-d H:i:s')]
    ],
    'where' => [
        'is_disabled' => ['custom', 'Yes'],
        'is_deleted' => ['custom', 'No'],
        'connection_id' => ['payload', 'connection_id']
    ],
    'validate' => [
		[
			'fn' => 'primaryKeyExist',
			'fnArgs' => [
                'table' => ['custom', getenv('connections')],
                'primary' => ['custom', 'connection_id'],
                'id' => ['payload', 'connection_id']
            ],
			'errorMessage' => 'Invalid Connection Id'
		],
		[
			'fn' => 'checkColumnValueExist',
			'fnArgs' => [
                'table' => ['custom', getenv('connections')],
                'column' => ['custom', 'is_deleted'],
                'columnValue' => ['custom', 'No'],
                'primary' => ['custom', 'connection_id'],
                'id' => ['payload', 'connection_id'],
            ],
			'errorMessage' => 'Record is deleted'
		],
		[
			'fn' => 'checkColumnValueExist',
			'fnArgs' => [
                'table' => ['custom', getenv('connections')],
                'column' => ['custom', 'is_disabled'],
                'columnValue' => ['custom', 'Yes'],
                'primary' => ['custom', 'connection_id'],
                'id' => ['payload', 'connection_id'],
            ],
			'errorMessage' => 'Record is already enabled'
		]
	]
];
