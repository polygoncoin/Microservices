<?php
namespace Microservices\public_html\Config\Queries\Auth\GlobalDB\PATCH\enable;

use Microservices\App\Constants;
use Microservices\App\DatabaseDataTypes;
use Microservices\App\Env;

return [
    '__QUERY__' => "UPDATE `{$Env::$clients}` SET __SET__ WHERE __WHERE__",
    '__SET__' => [
        ['column' => 'is_disabled', 'fetchFrom' => 'custom', 'fetchFromValue' => 'No'],
        ['column' => 'updated_by', 'fetchFrom' => 'userDetails', 'fetchFromValue' => 'user_id'],
        ['column' => 'updated_on', 'fetchFrom' => 'custom', 'fetchFromValue' => date('Y-m-d H:i:s')]
    ],
    '__WHERE__' => [
        ['column' => 'is_disabled', 'fetchFrom' => 'custom', 'fetchFromValue' => 'Yes'],
        ['column' => 'is_deleted', 'fetchFrom' => 'custom', 'fetchFromValue' => 'No'],
        ['column' => 'client_id', 'fetchFrom' => 'payload', 'fetchFromValue' => 'client_id', 'dataType' => DatabaseDataTypes::$INT]
    ],
    '__VALIDATE__' => [
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
			'errorMessage' => 'Record is deleted'
		],
		[
			'fn' => 'checkColumnValueExist',
			'fnArgs' => [
                'table' => ['custom', Env::$clients],
                'column' => ['custom', 'is_disabled'],
                'columnValue' => ['custom', 'Yes'],
                'primary' => ['custom', 'client_id'],
                'id' => ['payload', 'client_id', DatabaseDataTypes::$INT],
            ],
			'errorMessage' => 'Record is already enabled'
		]
	]
];
