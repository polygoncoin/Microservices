<?php
return [
    'all' => [
        'default' => [
            'query' => "SELECT * FROM `{$this->clientDB}`.`{$uriParams['table']}`",
            'payload' => [],
            'mode' => 'multipleRowFormat'
        ],
    ],
    "single" => [
        'default' => [
            'query' => "SELECT id, name FROM `{$this->clientDB}`.`{$uriParams['table']}` WHERE id = ?",
            'payload' => [$uriParams['id']],
            'mode' => 'singleRowFormat'//Single row returned.
        ],
    ]
][isset($uriParams['id'])?'single':'all'];
