<?php
return [
    'm006_master_client' => [
        'query' => "INSERT INTO `{$this->clientDB}`.`{$input['uriParams']['table']}` SET __SET__",
        'payload' => [
            //column => [payload|readOnlySession|uriParams|insertIdParams|{custom}, key|{value}],
            'name' => ['payload', 'name']
        ],
        'insertId' => 'clientId',
    ]
][$input['uriParams']['table']];
