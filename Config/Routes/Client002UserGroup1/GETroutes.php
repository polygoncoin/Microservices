<?php
namespace Microservices\Config\Routes\Client001UserGroup1;

use Microservices\App\Constants;
use Microservices\App\Env;
use Microservices\App\HttpRequest;

return array_merge(
    include_once Constants::$DOC_ROOT . '/Config/Routes/Common/Client/GETroutes.php',
    include_once Constants::$DOC_ROOT . '/Config/Routes/Common/Custom/GETroutes.php',
    include_once Constants::$DOC_ROOT . '/Config/Routes/Common/ThirdParty/GETroutes.php',
);
