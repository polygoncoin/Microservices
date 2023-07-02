<?php
return [
    'query' => "UPDATE `{$this->globalDB}`.`{$this->execPhpFunc(getenv('clients'))}` SET __SET__ WHERE __WHERE__",
    'payload' => [
        //column => [payload|readOnlySession|insertIdParams|{custom}, key|{value}],
        'name' => ['payload', 'name'],
        'updated_by' => ['readOnlySession', 'user_id'],
        'updated_on' => ['custom', date('Y-m-d H:i:s')]
    ],
    'where' => [
        'is_approved' => ['custom', 'Yes'],
        'is_deleted' => ['custom', 'No'],
        'client_id' => ['uriParams', 'client_id']
    ]
];
