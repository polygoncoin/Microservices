<?php
return [
    'query' => "INSERT INTO `{$this->globalDB}`.`{$this->execPhpFunc(getenv('links'))}` SET __SET__",
    'payload' => [
        //column => [payload|readOnlySession|insertIdParams|{custom}, key|{value}],
        'name' => ['payload', 'name'],
        'group_id' => ['payload', 'group_id'],
        'route_id' => ['payload', 'route_id'],
        'http_id' => ['payload', 'http_id'],
        'created_by' => ['readOnlySession', 'user_id'],
        'created_on' => ['custom', date('Y-m-d H:i:s')],
        'is_approved' => ['custom', 'No'],
        'is_disabled' => ['custom', 'No'],
        'is_deleted' => ['custom', 'No']
    ],
    'insertId' => 'link_id',
];
