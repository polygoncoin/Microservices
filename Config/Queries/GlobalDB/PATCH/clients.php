<?php
namespace Config\Queries\GlobalDB\PATCH;

use App\HttpRequest;

return [
    'query' => "UPDATE `{$this->globalDB}`.`{$this->execPhpFunc(getenv('clients'))}` SET __SET__ WHERE __WHERE__",
    '__CONFIG__' => [// [{payload/uriParams}, key/index, {REQUIRED}]
        ['payload', 'name', REQUIRED],
        ['uriParams', 'client_id', REQUIRED],
    ],
    '__SET__' => [
        //column => [payload|readOnlySession|uriParams|insertIdParams|{custom}, key|{value}],
        'name' => ['payload', 'name', REQUIRED],
        'updated_by' => ['readOnlySession', 'user_id'],
        'updated_on' => ['custom', date('Y-m-d H:i:s')]
    ],
    '__WHERE__' => [
        'is_approved' => ['custom', 'Yes'],
        'is_disabled' => ['custom', 'No'],
        'is_deleted' => ['custom', 'No'],
        'client_id' => ['uriParams', 'client_id']
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
	]
];
