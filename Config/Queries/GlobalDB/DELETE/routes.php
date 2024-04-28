<?php
namespace Config\Queries\GlobalDB\DELETE;

use App\HttpRequest;

return [
    'query' => "UPDATE `{$this->globalDB}`.`{$this->execPhpFunc(getenv('routes'))}` SET __SET__ WHERE __WHERE__",
    'payload' => [
        //column => [payload|readOnlySession|uriParams|insertIdParams|{custom}, key|{value}],
        'is_deleted' => ['custom', 'Yes'],
        'updated_by' => ['readOnlySession', 'user_id'],
        'updated_on' => ['custom', date('Y-m-d H:i:s')]
    ],
    'where' => [
        'is_deleted' => ['custom', 'No'],
        'route_id' => ['uriParams', 'route_id']
    ],
    'validate' => [
		[
			'fn' => 'primaryKeyExist',
			'fnArgs' => [
                'table' => ['custom', getenv('routes')],
                'primary' => ['custom', 'route_id'],
                'id' => ['payload', 'route_id']
            ],
			'errorMessage' => 'Invalid Route Id'
		],
		[
			'fn' => 'checkColumnValueExist',
			'fnArgs' => [
                'table' => ['custom', getenv('routes')],
                'column' => ['custom', 'is_deleted'],
                'columnValue' => ['custom', 'No'],
                'primary' => ['custom', 'route_id'],
                'id' => ['payload', 'route_id'],
            ],
			'errorMessage' => 'Record is already deleted'
		]
	]
];
