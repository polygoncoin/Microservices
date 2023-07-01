<?php
return [
    'query' => "UPDATE `{$this->globalDB}`.`{$this->execPhpFunc(getenv('groups'))}` SET __SET__ WHERE __WHERE__",
    'payload' => [
        //column => [payload|readOnlySession|insertIdParams|{custom}, key|{value}],
        'name' => ['payload', 'name'],
        'client_id' => ['payload', 'client_id'],
        'connection_id' => ['payload', 'connection_id'],
        'allowed_ips' => ['payload', 'allowed_ips'],
        'comments' => ['payload', 'comments'],
        'updated_by' => ['readOnlySession', 'user_id'],
        'updated_on' => ['custom', date('Y-m-d H:i:s')]
    ],
    'where' => [
        'group_id' => ['uriParams', 'group_id']
    ]
];
