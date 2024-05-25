<?php
namespace Config\Queries\GlobalDB\PATCH\disable;

use App\HttpRequest;

return [
    'query' => "UPDATE `{$this->globalDB}`.`{$this->execPhpFunc(getenv('users'))}` SET __SET__ WHERE __WHERE__",
    '__CONFIG__' => [// [{payload/uriParams}, key/index, {REQUIRED}]
        ['uriParams', 'user_id', REQUIRED],
    ],
    '__SET__' => [
        //column => [payload|readOnlySession|uriParams|insertIdParams|{custom}, key|{value}],
        'is_disabled' => ['custom', 'Yes'],
        'updated_by' => ['readOnlySession', 'user_id'],
        'updated_on' => ['custom', date('Y-m-d H:i:s')]
    ],
    '__WHERE__' => [
        'is_disabled' => ['custom', 'No'],
        'is_deleted' => ['custom', 'No'],
        'user_id' => ['payload', 'user_id']
    ],
    'validate' => [
		[
			'fn' => 'primaryKeyExist',
			'fnArgs' => [
                'table' => ['custom', getenv('users')],
                'primary' => ['custom', 'user_id'],
                'id' => ['payload', 'user_id']
            ],
			'errorMessage' => 'Invalid User Id'
		],
		[
			'fn' => 'checkColumnValueExist',
			'fnArgs' => [
                'table' => ['custom', getenv('users')],
                'column' => ['custom', 'is_deleted'],
                'columnValue' => ['custom', 'No'],
                'primary' => ['custom', 'user_id'],
                'id' => ['payload', 'user_id'],
            ],
			'errorMessage' => 'Record is deleted'
		],
		[
			'fn' => 'checkColumnValueExist',
			'fnArgs' => [
                'table' => ['custom', getenv('users')],
                'column' => ['custom', 'is_disabled'],
                'columnValue' => ['custom', 'No'],
                'primary' => ['custom', 'user_id'],
                'id' => ['payload', 'user_id'],
            ],
			'errorMessage' => 'Record is already disabled'
		]
	]
];
