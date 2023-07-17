<?php
define('EXPIRY_TIME', 3600);
define('__DOC_ROOT__', dirname(__DIR__));

require_once __DOC_ROOT__ . '/autoload.php';

// REQUEST_URI key in URL
define('REQUEST_URI', 'REQUEST_URI');

define('__REQUEST_URI__', '/' . trim($_GET[REQUEST_URI], '/'));

header('Content-Type: application/json; charset=utf-8');
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");

switch (true) {
    case __REQUEST_URI__ === '/login':
        App\Login::init();
        break;
    case strpos(__REQUEST_URI__, '/crons') === 0:
        // Check request not from proxy.
        if (!isset($_SERVER['REMOTE_ADDR'])) {
            die('Proxy requests are not supported.');
        }
        if ($_SERVER['REMOTE_ADDR'] !== getenv('cronRestrictedIp')) {
            die('Source IP is not supported.');
        }
        $__REQUEST_URI__ = explode('/', __REQUEST_URI__);
        if (
            isset($__REQUEST_URI__[2]) &&
            file_exists(__DOC_ROOT__ . "/Crons/{$__REQUEST_URI__[2]}.php")
        ) {
            eval('Crons\\' . $__REQUEST_URI__[2] . '::init($_SERVER[\'REQUEST_METHOD\'], __REQUEST_URI__);');
        } else {
            die('Invalid request.');
        }
        break;
    case __REQUEST_URI__ === '/reload':
        if (httpAuthentication()) {
            App\Reload::init();
        }
        break;
    default:
        App\Api::init();
        break;
}
