<?php
namespace Microservices\public_html\Config\Routes\Auth\ClientDB\Common\ThirdParty;

return [
    $Env::$thirdPartyRequestUriPrefix => [
        '{thirdParty:string}' => [
            '__FILE__' => false
        ]
    ]
];
