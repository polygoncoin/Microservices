<?php
namespace Microservices\Config\Queries\GlobalDB\DELETE;

use Microservices\App\Constants;
use Microservices\App\Env;
use Microservices\App\HttpRequest;

return [
    'query' => "UPDATE `{$Env::$globalDB}`.`{$Env::$connections}` SET __SET__ WHERE __WHERE__",
    '__CONFIG__' => [// [{payload/uriParams}, key/index, {Constants::$REQUIRED}]
        ['uriParams', 'connection_id', Constants::$REQUIRED],
    ],
    '__SET__' => [
        //column => [payload|readOnlySession|uriParams|insertIdParams|{custom}, key|{value}],
        'is_deleted' => ['custom', 'Yes'],
        'updated_by' => ['readOnlySession', 'user_id'],
        'updated_on' => ['custom', date('Y-m-d H:i:s')]
    ],
    '__WHERE__' => [
        'is_deleted' => ['custom', 'No'],
        'connection_id' => ['uriParams', 'connection_id']
    ],
    'validate' => [
		[
			'fn' => 'primaryKeyExist',
			'fnArgs' => [
                'table' => ['custom', Env::$connections],
                'primary' => ['custom', 'connection_id'],
                'id' => ['payload', 'connection_id']
            ],
			'errorMessage' => 'Invalid Connection Id'
		],
		[
			'fn' => 'checkColumnValueExist',
			'fnArgs' => [
                'table' => ['custom', Env::$connections],
                'column' => ['custom', 'is_deleted'],
                'columnValue' => ['custom', 'No'],
                'primary' => ['custom', 'connection_id'],
                'id' => ['payload', 'connection_id'],
            ],
			'errorMessage' => 'Record is already deleted'
		]
	]
];
