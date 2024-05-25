<?php
namespace Config\Queries\GlobalDB\PATCH\enable;

use App\HttpRequest;

return [
    'query' => "UPDATE `{$this->globalDB}`.`{$this->execPhpFunc(getenv('clients'))}` SET __SET__ WHERE __WHERE__",
    '__CONFIG__' => [// [{payload/uriParams}, key/index, {REQUIRED}]
        ['uriParams', 'client_id', REQUIRED],
    ],
    '__SET__' => [
        //column => [payload|readOnlySession|uriParams|insertIdParams|{custom}, key|{value}],
        'is_disabled' => ['custom', 'No'],
        'updated_by' => ['readOnlySession', 'user_id'],
        'updated_on' => ['custom', date('Y-m-d H:i:s')]
    ],
    '__WHERE__' => [
        'is_disabled' => ['custom', 'Yes'],
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
                'columnValue' => ['custom', 'Yes'],
                'primary' => ['custom', 'client_id'],
                'id' => ['payload', 'client_id'],
            ],
			'errorMessage' => 'Record is already enabled'
		]
	]
];
