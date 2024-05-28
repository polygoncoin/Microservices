<?php
namespace Config\Queries\GlobalDB\DELETE;

use App\Constants;
use App\Env;
use App\HttpRequest;

return [
    'query' => "UPDATE `{$this->globalDB}`.`{$this->execPhpFunc(getenv('https'))}` SET __SET__ WHERE __WHERE__",
    '__CONFIG__' => [// [{payload/uriParams}, key/index, {Constants::$REQUIRED}]
        ['uriParams', 'http_id', Constants::$REQUIRED],
    ],
    '__SET__' => [
        //column => [payload|readOnlySession|uriParams|insertIdParams|{custom}, key|{value}],
        'is_deleted' => ['custom', 'Yes'],
        'updated_by' => ['readOnlySession', 'user_id'],
        'updated_on' => ['custom', date('Y-m-d H:i:s')]
    ],
    '__WHERE__' => [
        'is_deleted' => ['custom', 'No'],
        'http_id' => ['uriParams', 'http_id']
    ],
    'validate' => [
		[
			'fn' => 'primaryKeyExist',
			'fnArgs' => [
                'table' => ['custom', getenv('https')],
                'primary' => ['custom', 'http_id'],
                'id' => ['payload', 'http_id']
            ],
			'errorMessage' => 'Invalid Http Id'
		],
		[
			'fn' => 'checkColumnValueExist',
			'fnArgs' => [
                'table' => ['custom', getenv('https')],
                'column' => ['custom', 'is_deleted'],
                'columnValue' => ['custom', 'No'],
                'primary' => ['custom', 'http_id'],
                'id' => ['payload', 'http_id'],
            ],
			'errorMessage' => 'Record is already deleted'
		]
	]
];
