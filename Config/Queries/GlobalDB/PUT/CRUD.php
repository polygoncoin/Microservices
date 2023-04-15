<?php
return [
    'm001_master_group' => [
        'default' => [
            'query' => "UPDATE {$this->globalDB}.m001_master_group SET col1 = ?, col2 = ? WHERE id = ?",
            'payload' => [
                $payload['col1'],
                $payload['col2'],
                $uriParams['id']
            ]
        ]
    ]
][$uriParams['table']];
