<?php
return [
    'query' => "UPDATE `{$this->globalDB}`.`{$this->execPhpFunc(getenv('https'))}` SET __SET__ WHERE __WHERE__",
    'payload' => [
        //column => [payload|readOnlySession|uriParams|insertIdParams|{custom}, key|{value}],
        'is_disabled' => ['custom', 'Yes'],
        'updated_by' => ['readOnlySession', 'user_id'],
        'updated_on' => ['custom', date('Y-m-d H:i:s')]
    ],
    'where' => [
        'is_disabled' => ['custom', 'No'],
        'is_deleted' => ['custom', 'No'],
        'http_id' => ['payload', 'http_id']
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
	]
];
