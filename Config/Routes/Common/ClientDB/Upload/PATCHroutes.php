<?php
namespace Microservices\Config\Routes\Common\ClientDB\Client;

return [
    'upload' => [
        '{module:string}' => [
            '{id:int|!0}'  => [
                '__file__' => false
            ]
        ]
    ]
];
