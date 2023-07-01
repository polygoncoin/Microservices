<?php
return [
    'all' => [
        'query' => "SELECT * FROM `{$this->globalDB}`.`{$this->execPhpFunc(getenv('routes'))}`",
        'where' => [],
        'mode' => 'multipleRowFormat'//Multiple rows returned.
    ],
    'single' => [
        'query' => "SELECT * FROM `{$this->globalDB}`.`{$this->execPhpFunc(getenv('routes'))}` WHERE __WHERE__",
        'where' => ['route_id' => ['uriParams','route_id']],
        'mode' => 'singleRowFormat'//Single row returned.
    ]
][isset($input['uriParams']['route_id'])?'single':'all'];
