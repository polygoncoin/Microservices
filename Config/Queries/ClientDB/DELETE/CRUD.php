<?php
namespace Config\Queries\ClientDB\DELETE;

use App\HttpRequest;

return [
    'registration' => array_merge(
        include __DOC_ROOT__ . '/Config/Queries/ClientDB/Common/Registration.php',
        [
            'payload' => [
                'is_deleted' => ['custom', 'Yes']
            ],
            'subQuery' => [
                'address' => [
                    'query' => "UPDATE `{$this->clientDB}`.`address` SET __SET__ WHERE __WHERE__",
                    'payload' => [
                        'is_deleted' => ['custom', 'Yes']
                    ],
                    'where' => [
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
            'payload' => [
                'is_deleted' => ['custom', 'Yes']
            ],
        ]
    )
][HttpRequest::$input['uriParams']['table']];
