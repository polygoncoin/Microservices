<?php
namespace Microservices\Config\Queries\ClientDB\POST;

use Microservices\App\Constants;
use Microservices\App\Env;
use Microservices\App\HttpRequest;

return [
    'query' => "INSERT INTO `{$Env::$clientDB}`.`address` SET __SET__",
    '__SET__' => [
        //column => [payload|readOnlySession|uriParams|insertIdParams|{custom}, key|{value}],
        'registration' => ['payload', 'registration_id'],
        'address' => ['payload', 'address'],
    ],
    'insertId' => 'address:id'
];
