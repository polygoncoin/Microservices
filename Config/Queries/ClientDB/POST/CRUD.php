<?php
return [
    'm001_master_group' => [
        'query' => "INSERT INTO {$this->clientDB}.{$uriParams['table']} SET __SET__",
        'payload' => [],
        'validate' => [
            [
                'fn' => 'validateRequired',
                'payloadKey' => 'username',
                'errorMessage' => ''
            ],
        ]
    ]
][$uriParams['table']];
