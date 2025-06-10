<?php
namespace Microservices\public_html\Config\Routes\Auth\ClientDB\Groups\Client001UserGroup1;

return array_merge (
    include $Constants::$PUBLIC_HTML . DIRECTORY_SEPARATOR . 'Config' . DIRECTORY_SEPARATOR . 'Routes' . DIRECTORY_SEPARATOR . 'Auth' . DIRECTORY_SEPARATOR . 'ClientDB' . DIRECTORY_SEPARATOR . 'Common' . DIRECTORY_SEPARATOR . 'PUTroutes.php',
    include $Constants::$PUBLIC_HTML . DIRECTORY_SEPARATOR . 'Config' . DIRECTORY_SEPARATOR . 'Routes' . DIRECTORY_SEPARATOR . 'Auth' . DIRECTORY_SEPARATOR . 'ClientDB' . DIRECTORY_SEPARATOR . 'Common' . DIRECTORY_SEPARATOR . 'Custom' . DIRECTORY_SEPARATOR . 'PUTroutes.php'
);
