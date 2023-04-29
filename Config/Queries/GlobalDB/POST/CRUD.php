<?php
return [
    'm006_master_client' => [
        'query' => "INSERT INTO `{$this->globalDB}`.`{$input['uriParams']['table']}` SET __SET__",
        'payload' => [
            //column => [payload|readOnlySession|insertIdParams|{custom}, key|{value}],
            'name' => ['payload', 'name']
        ],
        'insertId' => 'clientId',//Insert Id
    ]
][$input['uriParams']['table']];
