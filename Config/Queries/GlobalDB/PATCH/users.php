<?php
namespace Config\Queries\GlobalDB\PATCH;

use App\HttpRequest;

return [
    'query' => "UPDATE `{$this->globalDB}`.`{$this->execPhpFunc(getenv('users'))}` SET __SET__ WHERE __WHERE__",
    '__CONFIG__' => [// [{payload/uriParams}, key/index, {REQUIRED}]
        ['payload', 'name', REQUIRED],
        ['uriParams', 'user_id', REQUIRED],
    ],
    '__SET__' => [
        //column => [payload|readOnlySession|uriParams|insertIdParams|{custom}, key|{value}],
        'password_hash' => ['payload', 'password_hash'],
        'updated_by' => ['readOnlySession', 'user_id'],
        'updated_on' => ['custom', date('Y-m-d H:i:s')]
    ],
    '__WHERE__' => [
        'is_approved' => ['custom', 'Yes'],
        'is_disabled' => ['custom', 'No'],
        'is_deleted' => ['custom', 'No'],
        'user_id' => ['uriParams', 'user_id']
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
	]
];
