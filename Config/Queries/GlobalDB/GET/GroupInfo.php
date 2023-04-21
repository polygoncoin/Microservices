<?php
return [
    'query' => "SELECT id, name FROM {$this->globalDB}.m001_master_group LIMIT 0,1",
    'where' => [],
    'mode' => 'singleRowFormat',//Single row returned.
    'subQuery' => [
        'Clients' => [
            'query' => "SELECT * FROM {$this->globalDB}.m001_master_group",
            'where' => [],
            'mode' => 'multipleRowFormat'//Multiple rows returned.
        ],
        'Users' => [
            'query' => "SELECT * FROM {$this->globalDB}.m003_master_user",
            'where' => [],
            'mode' => 'multipleRowFormat'//Multiple rows returned.
        ]
    ]
];
