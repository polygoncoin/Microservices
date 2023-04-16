<?php
return [
    'm001_master_group' => [
        'default' => [
            'query' => "UPDATE {$this->clientDB}.m001_master_group SET {$payload['column_name']} = ? WHERE id = ?",
            'payload' => [
                $payload['column_value'],
                $uriParams['id']
            ]
        ]
    ]
][$uriParams['table']];
