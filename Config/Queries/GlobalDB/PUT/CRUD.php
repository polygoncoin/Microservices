<?php
return [
    'm006_master_client' => [
        'query' => "UPDATE `{$this->globalDB}`.`{$input['uriParams']['table']}` SET __SET__ WHERE __WHERE__",
        'payload' => [
            'name' => ['payload', 'name']
        ],
        'where' => [
            'id' => ['uriParams', 'id']
        ]
    ]
][$input['uriParams']['table']];
