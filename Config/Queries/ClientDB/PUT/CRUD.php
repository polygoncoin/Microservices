<?php
namespace Config\Queries\ClientDB\PUT;

use App\HttpRequest;

return [
    'registration' => array_merge(
        include __DOC_ROOT__ . '/Config/Queries/ClientDB/Common/Registration.php',
        [
            'payload' => [
                'password' => ['payload', 'password'],
                'email' => ['payload', 'email']
            ]
        ]
    ),
    'address' => array_merge(
        include __DOC_ROOT__ . '/Config/Queries/ClientDB/Common/Registration.php',
        [
            'payload' => [
                'address' => ['payload', 'address']
            ],
        ]
    )
][HttpRequest::$input['uriParams']['table']];
