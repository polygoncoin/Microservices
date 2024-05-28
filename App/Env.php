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
class Env
{
    public static $defaultDbDatabase = null;

    public static $cacheType = null;
    public static $cacheHostname = null;
    public static $cachePort = null;
    public static $cacheUsername = null;
    public static $cachePassword = null;
    public static $cacheDatabase = null;

    public static $dbType = null;
    public static $dbHostname = null;
    public static $dbPort = null;
    public static $dbUsername = null;
    public static $dbPassword = null;
    public static $dbDatabase = null;

    public static $ENVIRONMENT = null;
    public static $OUTPUT_PERFORMANCE_STATS = null;

    public static $allowConfigRequest = null;
    public static $isConfigRequest = null;

    public static $groups = null;
    public static $users = null;
    public static $connections = null;
    public static $clients = null;

    public static $maxPerpage = null;
    public static $cronRestrictedIp = null;

    public static $globalDB = null;
    public static $clientDB = null;

    public static function init()
    {
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
    }
}
