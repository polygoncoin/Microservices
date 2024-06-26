<?php
namespace Microservices\App;

use Microservices\App\Constants;

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
class Env
{
    static public $defaultDbDatabase = null;

    static public $cacheType = null;
    static public $cacheHostname = null;
    static public $cachePort = null;
    static public $cacheUsername = null;
    static public $cachePassword = null;
    static public $cacheDatabase = null;

    static public $dbType = null;
    static public $dbHostname = null;
    static public $dbPort = null;
    static public $dbUsername = null;
    static public $dbPassword = null;
    static public $dbDatabase = null;

    static public $ENVIRONMENT = null;
    static public $OUTPUT_PERFORMANCE_STATS = null;

    static public $allowConfigRequest = null;
    static public $isConfigRequest = null;

    static public $groups = null;
    static public $users = null;
    static public $connections = null;
    static public $clients = null;

    static public $maxPerpage = null;
    static public $cronRestrictedIp = null;

    static public $globalDB = null;
    static public $clientDB = null;

    static private $initialized = null;

    static public function init()
    {
        if (!is_null(self::$initialized)) return;
        
        // Initialize constants
        Constants::init();

        // Load .env
        $env = parse_ini_file(Constants::$DOC_ROOT . '/.env');
        foreach ($env as $key => $value) {
            putenv("{$key}={$value}");
        }

        self::$defaultDbDatabase = getenv('defaultDbDatabase');
        self::$ENVIRONMENT = getenv('ENVIRONMENT');
        self::$OUTPUT_PERFORMANCE_STATS = getenv('OUTPUT_PERFORMANCE_STATS');
        self::$allowConfigRequest = getenv('allowConfigRequest');
        self::$groups = getenv('groups');
        self::$users = getenv('users');
        self::$connections = getenv('connections');
        self::$clients = getenv('clients');
        self::$maxPerpage = getenv('maxPerpage');
        self::$cronRestrictedIp = getenv('cronRestrictedIp');
        self::$initialized = true;
    }
}
