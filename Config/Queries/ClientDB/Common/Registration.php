<?php
namespace Microservices\Config\Queries\ClientDB\Common;

use Microservices\App\Constants;
use Microservices\App\Env;
use Microservices\App\HttpRequest;

return [
    'query' => "UPDATE `{$Env::$clientDB}`.`registration` SET __SET__ WHERE __WHERE__",
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
