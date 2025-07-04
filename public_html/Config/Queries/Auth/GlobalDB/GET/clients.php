<?php
namespace Microservices\public_html\Config\Queries\Auth\GlobalDB\GET;

use Microservices\App\Constants;
use Microservices\App\DatabaseDataTypes;
use Microservices\App\Env;

return [
    'all' => [
        'countQuery' => "SELECT count(1) as `count` FROM `{$Env::$clients}` WHERE __WHERE__",
        '__QUERY__' => "SELECT * FROM `{$Env::$clients}` WHERE __WHERE__ ORDER BY client_id ASC",
        '__WHERE__' => [
            ['column' => 'is_approved', 'fetchFrom' => 'custom', 'fetchFromValue' => 'Yes'],
            ['column' => 'is_disabled', 'fetchFrom' => 'custom', 'fetchFromValue' => 'No'],
            ['column' => 'is_deleted', 'fetchFrom' => 'custom', 'fetchFromValue' => 'No']
            ],
        '__MODE__' => 'multipleRowFormat'
    ],
    'single' => [
        '__QUERY__' => "SELECT * FROM `{$Env::$clients}` WHERE __WHERE__",
        '__WHERE__' => [
            ['column' => 'is_approved', 'fetchFrom' => 'custom', 'fetchFromValue' => 'Yes'],
            ['column' => 'is_disabled', 'fetchFrom' => 'custom', 'fetchFromValue' => 'No'],
            ['column' => 'is_deleted', 'fetchFrom' => 'custom', 'fetchFromValue' => 'No'],
            ['column' => 'client_id', 'fetchFrom' => 'uriParams', 'fetchFromValue' => 'client_id']
        ],
        '__MODE__' => 'singleRowFormat'
    ],
][isset($this->c->httpRequest->session['uriParams']['client_id'])?'single':'all'];
