<?php
return [
    'all' => [
        'query' => "SELECT * FROM `{$this->globalDB}`.`{$this->execPhpFunc(getenv('https'))}`",
        'where' => [],
        'mode' => 'multipleRowFormat'//Multiple rows returned.
    ],
    'single' => [
        'query' => "SELECT * FROM `{$this->globalDB}`.`{$this->execPhpFunc(getenv('https'))}` WHERE __WHERE__",
        'where' => [
            'is_approved' => ['custom', 'Yes'],
            'is_deleted' => ['custom', 'No'],
            'http_id' => ['uriParams','http_id']
        ],
        'mode' => 'singleRowFormat'//Single row returned.
    ]
][isset($input['uriParams']['http_id'])?'single':'all'];
