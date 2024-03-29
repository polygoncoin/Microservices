<?php
namespace Config\Queries\GlobalDB\GET;

use App\HttpRequest;

return [
    'all' => [
        'countQuery' => "SELECT count(1) as `count` FROM `{$this->globalDB}`.`{$this->execPhpFunc(getenv('clients'))}` WHERE __WHERE__ ORDER BY client_id ASC",
        'query' => "SELECT * FROM `{$this->globalDB}`.`{$this->execPhpFunc(getenv('clients'))}` WHERE __WHERE__",
        'where' => [
            // 'is_approved' => ['custom', 'Yes'],
            // 'is_disabled' => ['custom', 'No'],
            'is_deleted' => ['custom', 'No'],
        ],
        'mode' => 'multipleRowFormat'//Multiple rows returned.
    ],
    'single' => [
        'query' => "SELECT * FROM `{$this->globalDB}`.`{$this->execPhpFunc(getenv('clients'))}` WHERE __WHERE__",
        'where' => [
            'is_approved' => ['custom', 'Yes'],
            'is_disabled' => ['custom', 'No'],
            'is_deleted' => ['custom', 'No'],
            'client_id' => ['uriParams','client_id']
        ],
        'mode' => 'singleRowFormat'//Single row returned.
    ]
][isset(HttpRequest::$input['uriParams']['client_id'])?'single':'all'];
