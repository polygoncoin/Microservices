<?php
return [
    'all' => [
        'query' => "SELECT * FROM `{$this->globalDB}`.`{$this->execPhpFunc(getenv('clients'))}`",
        'where' => [],
        'mode' => 'multipleRowFormat'//Multiple rows returned.
    ],
    'single' => [
        'query' => "SELECT * FROM `{$this->globalDB}`.`{$this->execPhpFunc(getenv('clients'))}` WHERE __WHERE__",
        'where' => ['client_id' => ['uriParams','client_id']],
        'mode' => 'singleRowFormat'//Single row returned.
    ]
][isset($input['uriParams']['client_id'])?'single':'all'];
