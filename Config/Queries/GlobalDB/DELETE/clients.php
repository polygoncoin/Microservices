<?php
namespace Microservices\Config\Queries\GlobalDB\DELETE;

use Microservices\App\Constants;
use Microservices\App\DatabaseDataTypes;
use Microservices\App\Env;

return [
    'query' => "UPDATE `{$Env::$clients}` SET __SET__ WHERE __WHERE__",
    '__CONFIG__' => [// [{payload/uriParams}, key/index, {Constants::$REQUIRED}]
        ['uriParams', 'client_id', DatabaseDataTypes::$INT, Constants::$REQUIRED],
    ],
    '__SET__' => [
        //column => [payload|userDetails|uriParams|insertIdParams|{custom}, key|{value}],
        'is_deleted' => ['custom', 'Yes'],
        'updated_by' => ['userDetails', 'user_id'],
        'updated_on' => ['custom', date('Y-m-d H:i:s')]
    ],
    '__WHERE__' => [
        'is_deleted' => ['custom', 'No'],
        'client_id' => ['uriParams', 'client_id', DatabaseDataTypes::$INT]
    ],
    'validate' => [
		[
			'fn' => 'primaryKeyExist',
			'fnArgs' => [
                'table' => ['custom', Env::$clients],
                'primary' => ['custom', 'client_id'],
                'id' => ['payload', 'client_id', DatabaseDataTypes::$INT]
            ],
			'errorMessage' => 'Invalid Client Id'
		],
		[
			'fn' => 'checkColumnValueExist',
			'fnArgs' => [
                'table' => ['custom', Env::$clients],
                'column' => ['custom', 'is_deleted'],
                'columnValue' => ['custom', 'No'],
                'primary' => ['custom', 'client_id'],
                'id' => ['payload', 'client_id', DatabaseDataTypes::$INT],
            ],
			'errorMessage' => 'Record is already deleted'
		]
	]
];
