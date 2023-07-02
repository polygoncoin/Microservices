<?php
return [
    'all' => [
        'query' => "SELECT * FROM `{$this->globalDB}`.`{$this->execPhpFunc(getenv('links'))}`",
        'where' => [],
        'mode' => 'multipleRowFormat'//Multiple rows returned.
    ],
    'single' => [
        'query' => "SELECT * FROM `{$this->globalDB}`.`{$this->execPhpFunc(getenv('links'))}` WHERE __WHERE__",
        'where' => [
            'is_approved' => ['custom', 'Yes'],
            'is_deleted' => ['custom', 'No'],
            'link_id' => ['uriParams','link_id']
        ],
        'mode' => 'singleRowFormat'//Single row returned.
    ]
][isset($input['uriParams']['link_id'])?'single':'all'];
