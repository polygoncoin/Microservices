<?php
namespace App\Servers\Cache;

use App\Constants;
use App\Env;
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
        switch (Env::$cacheType) {
            case 'Redis':
                self::$cache = new Redis(
                    Env::$cacheHostname,
                    Env::$cachePort,
                    Env::$cacheUsername,
                    Env::$cachePassword,
                    Env::$database
                );
                break;
            case 'MySQL':
                self::$cache = new MySQL(
                    Env::$cacheHostname,
                    Env::$cachePort,
                    Env::$cacheUsername,
                    Env::$cachePassword,
                    Env::$cacheDatabase
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