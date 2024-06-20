<?php
namespace Microservices\App;

/**
 * Constants
 *
 * Contains all constants related to Microservices
 *
 * @category   Constants
 * @package    Microservices
 * @author     Ramesh Narayan Jangid
 * @copyright  Ramesh Narayan Jangid
 * @version    Release: @1.0.0@
 * @since      Class available since Release 1.0.0
 */
class Constants
{
    static public $GET       = 'GET';
    static public $POST      = 'POST';
    static public $PUT       = 'PUT';
    static public $PATCH     = 'PATCH';
    static public $DELETE    = 'DELETE';

    static public $PRODUCTION = 1;
    static public $DEVELOPMENT = 0;

    static public $TOKEN_EXPIRY_TIME = 3600;  
    static public $REQUIRED = true;

    static public $DOC_ROOT = null;

    static public $REQUEST_METHOD = null;
    static public $HTTP_AUTHORIZATION = null;
    static public $REMOTE_ADDR = null;

    static public $ROUTE_URL_PARAM = 'r';
    static public $ROUTE = null;

    static private $initialized = null;

    static public function init()
    {
        if (!is_null(self::$initialized)) return;
        self::$DOC_ROOT = dirname(__DIR__ . '../');
        self::$REQUEST_METHOD = $_SERVER['REQUEST_METHOD'];

        if (isset($_SERVER['HTTP_AUTHORIZATION'])) {
            self::$HTTP_AUTHORIZATION = $_SERVER['HTTP_AUTHORIZATION'];
        }
        if (!isset($_SERVER['REMOTE_ADDR'])) {
            // If from a proxy server. then 404
            http_response_code(404);
        }
        self::$REMOTE_ADDR = $_SERVER['REMOTE_ADDR'];
        self::$ROUTE = '/' . trim($_GET[self::$ROUTE_URL_PARAM], '/');
        self::$initialized = true;
    }
}
