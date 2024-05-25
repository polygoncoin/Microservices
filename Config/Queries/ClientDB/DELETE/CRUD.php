<?php
namespace Config\Queries\ClientDB\DELETE;

use App\HttpRequest;

return [
    'registration' => array_merge(
        include __DOC_ROOT__ . '/Config/Queries/ClientDB/Common/Registration.php',
        [
            '__SET__' => [
                'is_deleted' => ['custom', 'Yes']
            ],
            'subQuery' => [
                'address' => [
                    'query' => "UPDATE `{$this->clientDB}`.`address` SET __SET__ WHERE __WHERE__",
                    '__CONFIG__' => [// [{payload/uriParams}, key/index, {REQUIRED}]
                        ['uriParams', 'id', REQUIRED],
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
        include __DOC_ROOT__ . '/Config/Queries/ClientDB/Common/Address.php',
        [
            '__SET__' => [
                'is_deleted' => ['custom', 'Yes']
            ],
        ]
    )
][HttpRequest::$input['uriParams']['table']];
