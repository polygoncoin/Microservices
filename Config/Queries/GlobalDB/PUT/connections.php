<?php
return [
    'query' => "UPDATE `{$this->globalDB}`.`{$this->execPhpFunc(getenv('connections'))}` SET __SET__ WHERE __WHERE__",
    'payload' => [
        //column => [payload|readOnlySession|insertIdParams|{custom}, key|{value}],
        'name' => ['payload', 'name'],
        'db_server_type' => ['payload', 'db_server_type'],
        'db_hostname' => ['payload', 'db_hostname'],
        'db_username' => ['payload', 'db_username'],
        'db_password' => ['payload', 'db_password'],
        'db_database' => ['payload', 'db_database'],
        'comments' => ['payload', 'comments'],
        'updated_by' => ['readOnlySession', 'user_id'],
        'updated_on' => ['custom', date('Y-m-d H:i:s')]
    ],
    'where' => [
        'connection_id' => ['uriParams', 'connection_id']
    ]
];
