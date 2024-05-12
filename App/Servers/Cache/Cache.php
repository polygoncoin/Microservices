<?php
namespace App\Servers\Cache;

use App\HttpRequest;
use App\HttpResponse;
use App\Logs;
use App\Servers\Cache\Redis;
use App\Servers\Cache\MySQL;

/**
 * Loading database class
 *
 * This class is built to handle MySQL database operation.
 *
 * @category   Cache
 * @package    Microservices
 * @author     Ramesh Narayan Jangid
 * @copyright  Ramesh Narayan Jangid
 * @version    Release: @1.0.0@
 * @since      Class available since Release 1.0.0
 */
class Cache
{
    /**
     * Cache Server Type
     *
     * @var string
     */
    public static $cacheType = null;

    /**
     * Cache hostname
     *
     * @var string
     */
    public static $hostname = null;

    /**
     * Cache port
     *
     * @var int
     */
    public static $port = null;

    /**
     * Cache username
     *
     * @var string
     */
    public static $username = null;

    /**
     * Cache password
     *
     * @var string
     */
    public static $password = null;

    /**
     * Cache database
     *
     * @var string
     */
    public static $database = null;

    /**
     * Cache connection
     *
     * @var object
     */
    public static $cache = null;

    /**
     * Database constructor
     * 
     * @param string $cacheType Cache Type
     * @param string $hostname  Hostname .env string
     * @param string $port      Port .env string
     * @param string $password  Password .env string
     * @param string $database  Database .env string
     * @return void
     */
    public static function connect(
        $cacheType,
        $hostname,
        $port,
        $username,
        $password,
        $database
    )
    {
        self::$cacheType = getenv($cacheType);
        self::$hostname = $hostname;
        self::$port = $port;
        self::$username = $username;
        self::$password = $password;
        self::$database = $database;

        if(self::$cacheType === 'Redis') {
            self::$cache = new Redis(
                $hostname,
                $port,
                $username,
                $password,
                $database
            );
        }
        if(self::$cacheType === 'MySQL') {
            self::$cache = new MySQL(
                $hostname,
                $port,
                $username,
                $password,
                $database
            );
        }
    }

    /**
     * Get Cache Object
     *
     * @return object
     */
    public static function getObject()
    {
        if (!is_null(self::$cache)) {
            return self::$cache;
        }
    }
}