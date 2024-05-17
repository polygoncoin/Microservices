<?php
ob_start();
define('PRODUCTION', 1);
define('DEVELOPMENT', 0);

define('EXPIRY_TIME', 3600);
define('__DOC_ROOT__', dirname(__DIR__));
define('REQUIRED', true);

require_once __DOC_ROOT__ . '/autoload.php';

define('ENVIRONMENT', getenv('ENVIRONMENT'));
define('OUTPUT_PERFORMANCE_STATS', getenv('OUTPUT_PERFORMANCE_STATS'));

if (OUTPUT_PERFORMANCE_STATS) {
    $start_time = microtime(true);
}

// REQUEST_URI key in URL
define('ROUTE_URL_PARAM', 'r');

define('ROUTE', '/' . trim($_GET[ROUTE_URL_PARAM], '/'));

header('Content-Type: application/json;charset=utf-8');
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");

$jsonObj = App\HttpResponse::getJsonObject();
$jsonObj->startAssoc();
//$jsonObj->addKeyValue('Status', 200);
$jsonObj->startAssoc('Output');

switch (true) {
    case ROUTE === '/login':
        App\Login::init();
        break;
    case strpos(ROUTE, '/crons') === 0:
        // Check request not from proxy.
        if (!isset($_SERVER['REMOTE_ADDR'])) {
            die('Proxy requests are not supported.');
        }
        if ($_SERVER['REMOTE_ADDR'] !== getenv('cronRestrictedIp')) {
            die('Source IP is not supported.');
        }
        $routeArr = explode('/', ROUTE);
        if (
            isset($routeArr[2]) &&
            file_exists(__DOC_ROOT__ . "/Crons/{$routeArr[2]}.php")
        ) {
            eval('Crons\\' . $routeArr[2] . '::init(ROUTE);');
        } else {
            die('Invalid request.');
        }
        break;
    case ROUTE === '/reload':
        if (httpAuthentication()) {
            App\Reload::init();
        }
        break;
    default:
        $api = new App\Api();
        $api->init();
        break;
}

$jsonObj->endAssoc();

if (OUTPUT_PERFORMANCE_STATS) {
    $end_time = microtime(true);
    $time = ($end_time - $start_time) * 1000;
    $memory = (memory_get_peak_usage()/1000);

    $jsonObj->startAssoc('Stats');
    $jsonObj->startAssoc('Performance');
    $jsonObj->encode(
        [
            'total-time-taken' => ceil($time) . ' ms',
            'peak-memory-usage' => ceil($memory) . ' KB'
        ]
    );
    $jsonObj->endAssoc();
    $jsonObj->endAssoc();
}

$jsonObj->endAssoc();
$jsonObj = null;
