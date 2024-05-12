<?php
namespace Config\Queries\ClientDB\GET;

use App\HttpRequest;
//return represents root for hierarchyData
return [
    'query' => "SELECT * FROM `{$this->clientDB}`.`registration` WHERE __WHERE__",
    'where' => [
        'is_deleted' => ['custom', 'No']
    ],
    'mode' => 'multipleRowFormat',//Multiple rows returned.
    'subQuery' => [
        'reg-address' => [
            'query' => "SELECT * FROM `{$this->clientDB}`.`address` WHERE __WHERE__",
            'where' => [
                'is_deleted' => ['custom', 'No'],
                'registration_id' => ['hierarchyData', 'root:id'],
            ],
            'mode' => 'multipleRowFormat',//Multiple rows returned.
        ]
    ],
    'useHierarchy' => true
];
