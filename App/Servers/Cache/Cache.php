<?php
namespace Microservices\App\Servers\Cache;

use Microservices\App\Constants;
use Microservices\App\Env;
use Microservices\App\Servers\Cache\Redis;
use Microservices\App\Servers\Cache\MySQL;

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
    static public $cache = null;

    /**
     * Database constructor
     * 
     * @return void
     */
    static public function connect()
    {
        switch (Env::$cacheType) {
            case 'Redis':
                self::$cache = new Redis(
                    Env::$cacheHostname,
                    Env::$cachePort,
                    Env::$cacheUsername,
                    Env::$cachePassword,
                    Env::$cacheDatabase
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
    static public function getObject()
    {
        if (is_null(self::$cache)) {
            self::connect();
        }
        return self::$cache;
    }
}