<?php
return [
    'all' => [
        'default' => [
            'query' => "SELECT * FROM `{$this->globalDB}`.`{$uriParams['table']}`",
            'payload' => [],
            'mode' => 'multipleRowFormat'
        ],
    ],
    "single" => [
        'default' => [
            'query' => "SELECT * FROM `{$this->globalDB}`.`{$uriParams['table']}` WHERE id = ?",
            'payload' => [isset($uriParams['id'])?$uriParams['id']:0],
            'mode' => 'singleRowFormat'//Single row returned.
        ],
    ]
][isset($uriParams['id'])?'single':'all'];
