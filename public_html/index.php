<?php
define('__DOC_ROOT__', dirname(__DIR__));

require_once __DOC_ROOT__ . '/autoload.php';

define('__REQUEST_URI__', trim($_GET['REQUEST_URI'], '/'));

header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json; charset=utf-8');

switch (__REQUEST_URI__) {
    case '/login':
        App/Login::init($authirizationHeader, $httpMethod, $requestIP);
        break;
    case '/reload':
        App/Reload::init($authirizationHeader, $httpMethod, $requestIP);
        break;
    default:
        App/Api::init($authirizationHeader, $httpMethod, $requestIP);
        break;
}
