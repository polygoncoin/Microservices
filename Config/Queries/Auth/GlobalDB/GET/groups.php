<?php
namespace Microservices\Config\Queries\Auth\GlobalDB\GET;

use Microservices\App\Constants;
use Microservices\App\DatabaseDataTypes;
use Microservices\App\Env;

return [
    'all' => [
        'query' => "SELECT * FROM `{$Env::$groups}` WHERE __WHERE__ ORDER BY group_id ASC",
        '__WHERE__' => [
            'is_approved' => ['custom', 'Yes'],
            'is_disabled' => ['custom', 'No'],
            'is_deleted' => ['custom', 'No'],
        ],
        'mode' => 'multipleRowFormat'
    ],
    'single' => [
        'query' => "SELECT * FROM `{$Env::$groups}` WHERE __WHERE__",
        '__WHERE__' => [
            'is_approved' => ['custom', 'Yes'],
            'is_disabled' => ['custom', 'No'],
            'is_deleted' => ['custom', 'No'],
            'group_id' => ['uriParams','group_id']
        ],
        'mode' => 'singleRowFormat'
    ]
][isset($this->c->httpRequest->session['uriParams']['group_id'])?'single':'all'];
