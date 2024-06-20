<?php
namespace Microservices\Config\Queries\ClientDB\PUT;

use Microservices\App\Constants;
use Microservices\App\Env;
use Microservices\App\HttpRequest;

return [
    'query' => "UPDATE `{$Env::$clientDB}`.`registration` SET username = :username WHERE username = :username_new AND is_deleted = :is_deleted AND id = :id",
    '__CONFIG__' => [// [{payload/uriParams}, key/index, {Constants::$REQUIRED}]
        ['payload', 'username', Constants::$REQUIRED],
        ['uriParams', 'id', Constants::$REQUIRED],
    ],
    '__SET__' => [
        'username' => ['payload', 'username'],
    ],
    '__WHERE__' => [
        'username_new' => ['payload', 'username'],
        'is_deleted' => ['custom', 'No'],
        'id' => ['uriParams', 'id']
    ],
    'validate' => [
		[
			'fn' => 'primaryKeyExist',
			'fnArgs' => [
                'table' => ['custom', 'registration'],
                'primary' => ['custom', 'id'],
                'id' => ['uriParams', 'id']
            ],
			'errorMessage' => 'Invalid registration id'
		],
	]
];
