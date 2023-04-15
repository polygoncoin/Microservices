<?php
return [
    'm001_master_group' => [
        'all' => [
            'default' => [
                'query' => "SELECT id, name FROM {$this->globalDB}.m001_master_group",
                'payload' => [],
                'mode' => 'multipleRowFormat'
            ],
        ],
        "single" => [
            'default' => [
                'query' => "SELECT id, name FROM {$this->globalDB}.m001_master_group WHERE id = ?",
                'payload' => [$uriParams['id']],
                'mode' => 'singleRowFormat'//Single row returned.
            ],
        ]
    ]
][$uriParams['table']][isset($uriParams['id'])?'single':'all'];
