<?php
return [
    'query' => "UPDATE `{$this->globalDB}`.`{$this->execPhpFunc(getenv('users'))}` SET __SET__ WHERE __WHERE__",
    'payload' => [
        //column => [payload|readOnlySession|insertIdParams|{custom}, key|{value}],
        'username' => ['payload', 'username'],
        'password_hash' => ['payload', 'password_hash'],
        'group_id' => ['payload', 'group_id'],
        'comments' => ['payload', 'comments'],
        'updated_by' => ['readOnlySession', 'user_id'],
        'updated_on' => ['custom', date('Y-m-d H:i:s')]
    ],
    'where' => [
        'user_id' => ['uriParams', 'user_id']
    ]
];
