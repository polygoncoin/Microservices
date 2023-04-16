<?php
return [
    'm001_master_group' => [
        'default' => [
            'query' => "UPDATE {$this->clientDB}.m001_master_group SET is_deleted = 1 WHERE id = ?",
            'payload' => [
                $uriParams['id']
            ]
        ]
    ]
][$uriParams['table']];
