<?php
define('EXPIRY_TIME', 3600);
define('__DOC_ROOT__', dirname(__DIR__));
define('REQUIRED', true);

require_once __DOC_ROOT__ . '/autoload.php';

// REQUEST_URI key in URL
define('ROUTE_URL_PARAM', 'REQUEST_URI');

define('ROUTE', '/' . trim($_GET[ROUTE_URL_PARAM], '/'));

header('Content-Type: application/json;charset=utf-8');
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");

ob_start();

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
        App\Api::init();
        break;
}
