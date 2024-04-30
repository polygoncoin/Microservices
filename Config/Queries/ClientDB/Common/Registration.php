<?php
namespace Config\Queries\ClientDB\Common;

use App\HttpRequest;

return [
    'query' => "UPDATE `{$this->clientDB}`.`registration` SET __SET__ WHERE __WHERE__",
    'where' => [
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