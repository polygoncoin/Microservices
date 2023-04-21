<?php
return [
    'all' => [
        'query' => "SELECT * FROM `{$this->globalDB}`.`{$input['uriParams']['table']}`",
        'where' => [],
        'mode' => 'multipleRowFormat'
    ],
    "single" => [
        'query' => "SELECT * FROM `{$this->globalDB}`.`{$input['uriParams']['table']}` WHERE id = ?",
        'where' => ['id' => ['uriParams','id']],
        'mode' => 'singleRowFormat'//Single row returned.
    ]
][isset($input['uriParams']['id'])?'single':'all'];
