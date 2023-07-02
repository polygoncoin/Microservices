<?php
return [
    'all' => [
        'query' => "SELECT * FROM `{$this->globalDB}`.`{$this->execPhpFunc(getenv('routes'))}`",
        'where' => [],
        'mode' => 'multipleRowFormat'//Multiple rows returned.
    ],
    'single' => [
        'query' => "SELECT * FROM `{$this->globalDB}`.`{$this->execPhpFunc(getenv('routes'))}` WHERE __WHERE__",
        'where' => [
            'is_approved' => ['custom', 'Yes'],
            'is_deleted' => ['custom', 'No'],
            'route_id' => ['uriParams','route_id']
        ],
        'mode' => 'singleRowFormat'//Single row returned.
    ]
][isset($input['uriParams']['route_id'])?'single':'all'];
