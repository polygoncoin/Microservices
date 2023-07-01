<?php
return [
    'query' => "UPDATE `{$this->globalDB}`.`{$this->execPhpFunc(getenv('links'))}` SET __SET__ WHERE __WHERE__",
    'payload' => [
        //column => [payload|readOnlySession|insertIdParams|{custom}, key|{value}],
        'is_deleted' => ['custom', 'Yes'],
        'updated_by' => ['readOnlySession', 'user_id'],
        'updated_on' => ['custom', date('Y-m-d H:i:s')]
    ],
    'where' => [
        'link_id' => ['uriParams', 'link_id']
    ]
];
