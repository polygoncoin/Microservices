<?php
define('__DOC_ROOT__', dirname(__DIR__));

require_once __DOC_ROOT__ . '/autoload.php';

header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json; charset=utf-8');

header('Content-Type: application/json; charset=utf-8');

App/Reload::init($authirizationHeader, $httpMethod, $requestIP);
