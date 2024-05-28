<?php
namespace Config\Queries\ClientDB\PATCH;

use App\Constants;
use App\HttpRequest;

return [
    'registration' => array_merge(
        include Constants::$__DOC_ROOT__ . '/Config/Queries/ClientDB/Common/Registration.php',
        [
            '__SET__' => [
                'username' => ['payload', 'username']
            ]
        ]
    ),
    'address' => array_merge(
        include Constants::$__DOC_ROOT__ . '/Config/Queries/ClientDB/Common/Address.php',
        [
            '__SET__' => [
                'address' => ['payload', 'address']
            ],
        ]
    )
][HttpRequest::$input['uriParams']['table']];
