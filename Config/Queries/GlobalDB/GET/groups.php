<?php
return [
    'all' => [
        'query' => "SELECT * FROM `{$this->globalDB}`.`{$this->execPhpFunc(getenv('groups'))}`",
        'where' => [],
        'mode' => 'multipleRowFormat'//Multiple rows returned.
    ],
    'single' => [
        'query' => "SELECT * FROM `{$this->globalDB}`.`{$this->execPhpFunc(getenv('groups'))}` WHERE __WHERE__",
        'where' => ['group_id' => ['uriParams','group_id']],
        'mode' => 'singleRowFormat'//Single row returned.
    ]
][isset($input['uriParams']['group_id'])?'single':'all'];
