<?php
namespace Config\Queries\ClientDB\POST;

use App\Constants;
use App\Env;
use App\HttpRequest;

return [
    'query' => "INSERT INTO `{$Env::$clientDB}`.`address` SET __SET__",
    '__SET__' => [
        //column => [payload|readOnlySession|uriParams|insertIdParams|{custom}, key|{value}],
        'registration' => ['payload', 'registration_id'],
        'address' => ['payload', 'address'],
    ],
    'insertId' => 'address:id'
];
