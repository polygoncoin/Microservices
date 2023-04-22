<?php
return [
    'm006_master_client' => [
        'query' => "INSERT INTO {$this->clientDB}.{$input['uriParams']['table']} SET __SET__",
        'payload' => [
            //column => [payload|readOnlySession|insertIdParams|{custom} => key|{value}],
            'name' => ['payload', 'name']
        ],
        'insertId' => 'm006_master_client:id',
    ]
][$input['uriParams']['table']];
