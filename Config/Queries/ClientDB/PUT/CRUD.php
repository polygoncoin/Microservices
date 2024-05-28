<?php
namespace Config\Queries\ClientDB\PUT;

use App\Constants;
use App\Env;
use App\HttpRequest;

return [
    'registration' => array_merge(
        include Constants::$DOC_ROOT . '/Config/Queries/ClientDB/Common/Registration.php',
        [
            '__SET__' => [
                'firstname' => ['payload', 'firstname'],
                'lastname' => ['payload', 'lastname'],
                'email' => ['payload', 'email']
            ]
        ]
    ),
    'address' => array_merge(
        include Constants::$DOC_ROOT . '/Config/Queries/ClientDB/Common/Address.php',
        [
            '__SET__' => [
                'address' => ['payload', 'address']
            ],
        ]
    )
][HttpRequest::$input['uriParams']['table']];
