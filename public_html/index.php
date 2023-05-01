<?php
define('EXPIRY_TIME', 3600);
define('__DOC_ROOT__', dirname(__DIR__));

require_once __DOC_ROOT__ . '/autoload.php';

define('__REQUEST_URI__', '/' . trim($_GET['REQUEST_URI'], '/'));

header('Content-Type: application/json; charset=utf-8');

switch (__REQUEST_URI__) {
    case '/login':
        App\Login::init();
        break;
    case '/cache':
        App\CacheApi::init();
        break;
    case '/migrate':
        if (httpAuthentication()) {
            App\Migration::init();
        }
        break;
    case '/reload':
        if (httpAuthentication()) {
            App\Reload::init();
        }
        break;
    default:
        App\Api::init();
        break;
}
