<?php
return [
    'm006_master_client' => [
        'query' => "UPDATE `{$this->clientDB}`.`{$input['uriParams']['table']}` SET __SET__ WHERE __WHERE__",
        'payload' => [
            'name' => ['payload', 'name']
        ],
        'where' => [
            'id' => ['uriParams', 'id']
        ]
    ]
][$input['uriParams']['table']];
