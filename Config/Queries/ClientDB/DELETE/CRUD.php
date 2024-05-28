<?php
namespace Config\Queries\ClientDB\DELETE;

use App\Constants;
use App\Env;
use App\HttpRequest;

return [
    'registration' => array_merge(
        include Constants::$DOC_ROOT . '/Config/Queries/ClientDB/Common/Registration.php',
        [
            '__SET__' => [
                'is_deleted' => ['custom', 'Yes']
            ],
            'subQuery' => [
                'address' => [
                    'query' => "UPDATE `{$Env::$clientDB}`.`address` SET __SET__ WHERE __WHERE__",
                    '__CONFIG__' => [// [{payload/uriParams}, key/index, {Constants::$REQUIRED}]
                        ['uriParams', 'id', Constants::$REQUIRED],
                    ],
                    '__SET__' => [
                        'is_deleted' => ['custom', 'Yes']
                    ],
                    '__WHERE__' => [
                        'is_deleted' => ['custom', 'No'],
                        'registration_id' => ['uriParams', 'id']
                    ]
                ]
            ]
        ]
    ),
    'address' => array_merge(
        include Constants::$DOC_ROOT . '/Config/Queries/ClientDB/Common/Address.php',
        [
            '__SET__' => [
                'is_deleted' => ['custom', 'Yes']
            ],
        ]
    )
][HttpRequest::$input['uriParams']['table']];
