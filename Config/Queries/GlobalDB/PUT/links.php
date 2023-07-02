<?php
return [
    'query' => "UPDATE `{$this->globalDB}`.`{$this->execPhpFunc(getenv('links'))}` SET __SET__ WHERE __WHERE__",
    'payload' => [
        //column => [payload|readOnlySession|insertIdParams|{custom}, key|{value}],
        'name' => ['payload', 'name'],
        'group_id' => ['payload', 'group_id'],
        'route_id' => ['payload', 'route_id'],
        'http_id' => ['payload', 'http_id'],
        'updated_by' => ['readOnlySession', 'user_id'],
        'updated_on' => ['custom', date('Y-m-d H:i:s')],
    ],
    'where' => [
        'is_approved' => ['custom', 'Yes'],
        'is_deleted' => ['custom', 'No'],
        'link_id' => ['uriParams', 'link_id']
    ]
];
