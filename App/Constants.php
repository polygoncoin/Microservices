<?php
namespace App;

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
    public static $GET       = 'GET';
    public static $POST      = 'POST';
    public static $PUT       = 'PUT';
    public static $PATCH     = 'PATCH';
    public static $DELETE    = 'DELETE';

    public static $PRODUCTION = 1;
    public static $DEVELOPMENT = 0;

    public static $TOKEN_EXPIRY_TIME = 3600;  
    public static $REQUIRED = true;

    public static $DOC_ROOT = null;

    public static $REQUEST_METHOD = null;
    public static $HTTP_AUTHORIZATION = null;
    public static $REMOTE_ADDR = null;

    public static $ROUTE_URL_PARAM = 'r';
    public static $ROUTE = null;

    public static function init()
    {
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
    }
}
