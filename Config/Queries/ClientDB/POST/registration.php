<?php
namespace Config\Queries\ClientDB\POST;

use App\HttpRequest;

return [
    'query' => "INSERT INTO `{$this->clientDB}`.`registration` SET __SET__",
    'payload' => [
        //column => [payload|readOnlySession|uriParams|insertIdParams|{custom}, key|{value}],
        'username' => ['payload', 'username', REQUIRED],
        'password' => ['payload', 'password', REQUIRED],
        'email' => ['custom', date('Y-m-d H:i:s')]    
    ],
    'insertId' => 'registrationId'
];
