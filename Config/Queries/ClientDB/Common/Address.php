<?php
namespace Config\Queries\ClientDB\Common;

use App\HttpRequest;

return [
    'query' => "UPDATE `{$this->clientDB}`.`address` SET __SET__ WHERE __WHERE__",
    '__CONFIG__' => [// [{payload/uriParams}, key/index, {REQUIRED}]
        ['uriParams', 'id', REQUIRED],
    ],
    '__WHERE__' => [
        'is_deleted' => ['custom', 'No'],
        'id' => ['uriParams', 'id']
    ],
    'validate' => [
		[
			'fn' => 'primaryKeyExist',
			'fnArgs' => [
                'table' => ['custom', 'address'],
                'primary' => ['custom', 'id'],
                'id' => ['uriParams', 'id']
            ],
			'errorMessage' => 'Invalid address id'
		],
	]

];
