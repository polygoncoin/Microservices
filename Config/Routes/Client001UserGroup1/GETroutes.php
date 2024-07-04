<?php
namespace Microservices\Config\Routes\Client001UserGroup1;

use Microservices\App\Constants;
use Microservices\App\Env;
use Microservices\App\HttpRequest;

return array_merge(
    include Constants::$DOC_ROOT . '/Config/Routes/Common/ClientDB/GETroutes.php',
    include Constants::$DOC_ROOT . '/Config/Routes/Common/ClientDB/Custom/GETroutes.php',
    include Constants::$DOC_ROOT . '/Config/Routes/Common/ClientDB/ThirdParty/GETroutes.php'
);
