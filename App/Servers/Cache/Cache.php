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
     * @return void
     */
    public static function connect()
    {
        switch (getenv(self::$cacheType)) {
            case 'Redis':
                self::$cache = new Redis(
                    self::$hostname,
                    self::$port,
                    self::$username,
                    self::$password,
                    self::$database
                );
                break;
            case 'MySQL':
                self::$cache = new MySQL(
                    self::$hostname,
                    self::$port,
                    self::$username,
                    self::$password,
                    self::$database
                );
                break;
        }
    }

    /**
     * Get Cache Object
     *
     * @return object
     */
    public static function getObject()
    {
        if (is_null(self::$cache)) {
            self::connect();
        }
        return self::$cache;
    }
}