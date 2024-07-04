<?php
namespace Microservices\Config\Routes\Common\ClientDB\Client;

use Microservices\App\Constants;
use Microservices\App\Env;
use Microservices\App\HttpRequest;

return [
    'upload' => [
        '{module}:string' => [
            '{id:int}'  => [
                '__file__' => false
            ]
        ]
    ]
];
