<?php
namespace Config\Queries\GlobalDB\PATCH\enable;

use App\HttpRequest;

return [
    'query' => "UPDATE `{$this->globalDB}`.`{$this->execPhpFunc(getenv('groups'))}` SET __SET__ WHERE __WHERE__",
    '__CONFIG__' => [// [{payload/uriParams}, key/index, {REQUIRED}]
        ['uriParams', 'group_id', REQUIRED],
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
        'group_id' => ['payload', 'group_id']
    ],
    'validate' => [
		[
			'fn' => 'primaryKeyExist',
			'fnArgs' => [
                'table' => ['custom', getenv('groups')],
                'primary' => ['custom', 'group_id'],
                'id' => ['payload', 'group_id']
            ],
			'errorMessage' => 'Invalid Group Id'
		],
		[
			'fn' => 'checkColumnValueExist',
			'fnArgs' => [
                'table' => ['custom', getenv('groups')],
                'column' => ['custom', 'is_deleted'],
                'columnValue' => ['custom', 'No'],
                'primary' => ['custom', 'group_id'],
                'id' => ['payload', 'group_id'],
            ],
			'errorMessage' => 'Record is deleted'
		],
		[
			'fn' => 'checkColumnValueExist',
			'fnArgs' => [
                'table' => ['custom', getenv('groups')],
                'column' => ['custom', 'is_disabled'],
                'columnValue' => ['custom', 'Yes'],
                'primary' => ['custom', 'group_id'],
                'id' => ['payload', 'group_id'],
            ],
			'errorMessage' => 'Record is already enabled'
		]
	]
];
