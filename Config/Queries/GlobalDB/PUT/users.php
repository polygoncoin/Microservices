<?php
namespace Config\Queries\GlobalDB\PUT;

use App\HttpRequest;

return [
    'query' => "UPDATE `{$this->globalDB}`.`{$this->execPhpFunc(getenv('users'))}` SET __SET__ WHERE __WHERE__",
    '__CONFIG__' => [// [{payload/uriParams}, key/index, {REQUIRED}]
        ['payload', 'name'],
        ['payload', 'comments'],
        ['uriParams', 'user_id', REQUIRED]
    ],
    '__SET__' => [
        //column => [payload|readOnlySession|uriParams|insertIdParams|{custom}, key|{value}],
        'username' => ['payload', 'username'],
        'password_hash' => ['function', function() {
            return password_hash(HttpRequest::$input['payload']['password'], PASSWORD_DEFAULT);
         }],
        'group_id' => ['payload', 'group_id'],
        'comments' => ['payload', 'comments'],
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
