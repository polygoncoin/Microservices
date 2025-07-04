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
    static public $PUBLIC_HTML = null;
    static public $ROUTE_URL_PARAM = 'r';

    static private $initialized = false;

    static public function init()
    {
        if (self::$initialized) return;

        self::$DOC_ROOT = dirname(__DIR__ . '..' . DIRECTORY_SEPARATOR);
        self::$PUBLIC_HTML = self::$DOC_ROOT . DIRECTORY_SEPARATOR . 'public_html';
        self::$initialized = true;
    }
}
