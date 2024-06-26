<?php
namespace Microservices\Config\Routes\Common\ThirdParty;

use Microservices\App\Constants;
use Microservices\App\Env;
use Microservices\App\HttpRequest;

return [
    'thirdParty' => [
        '{thirdParty:string}' => [
            '__file__' => false
        ]
    ]
];
