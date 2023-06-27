<?php
namespace App\Servers\Cache;

use App\HttpErrorResponse;

/**
 * Loading Redis server
 *
 * This class is built to handle redis cache operation.
 *
 * @category   Cache - Redis
 * @package    Microservices
 * @author     Ramesh Narayan Jangid
 * @copyright  Ramesh Narayan Jangid
 * @license    http://www.php.net/license/3_01.txt  PHP License 3.01
 * @version    Release: @1.0.0@
 * @since      Class available since Release 1.0.0
 */
class Redis
{
    /**
     * Cache hostname
     *
     * @var string
     */
    private $hostname = null;

    /**
     * Cache port
     *
     * @var int
     */
    private $port = null;

    /**
     * Cache password
     *
     * @var string
     */
    private $password = null;

    /**
     * Cache database
     *
     * @var string
     */
    private $database = null;

    /**
     * Cache connection
     *
     * @var object
     */
    private $redis = null;

    /**
     * Cache constructor
     */
    public function __construct(
        $hostname,
        $port,
        $password,
        $database = null
    )
    {   
        $this->hostname = $hostname;
        $this->port = $port;
        $this->password = $password;
        if (!is_null($database)) {
            $this->database = $database;
        }
    }

    /**
     * Cache connection
     *
     * @param string $mode Can be one of string among master/slave
     * @return void
     */
    function connect()
    {
        if (!is_null($this->redis)) return;
        try {
            $this->redis = new \Redis();
            //Connecting to Redis
            $this->redis->connect(getenv($this->hostname), getenv($this->port), 1, NULL, 100);
            $this->redis->auth(getenv($this->password));
            if (!is_null($this->database)) {
                $this->useDatabase($this->database);
            }
            if (!$this->redis->ping()) {
                HttpErrorResponse::return5xx(501, 'Unable to ping to cache server');
            }
        } catch (\Exception $e) {
            HttpErrorResponse::return5xx(501, 'Unable to connect to cache server');
        }
    }

    /**
     * Use Database
     *
     * @param string $database Database .env string
     * @return void
     */
    public function useDatabase($database)
    {
        $this->connect();
        $this->redis->select(getenv($this->database));
    }

    /**
     * Checks if cache key exist
     *
     * @param string $key Cache key
     * @return boolean
     */
    public function cacheExists($key)
    {
        $this->connect();
        return $this->redis->exists($key);
    }

    /**
     * Get cache on basis of key
     *
     * @param string $key Cache key
     * @return string
     */
    public function getCache($key)
    {
        $this->connect();
        return $this->redis->get($key);
    }

    /**
     * Set cache on basis of key
     *
     * @param string $key    Cache key
     * @param string $value  Cache value
     * @param int    $expire Seconds to expire. Default 0 - doesnt expire
     * @return int
     */
    public function setCache($key, $value, $expire = null)
    {
        $this->connect();
        if (is_null($expire)) {
            return $this->redis->set($key, $value);
        } else {
            return $this->redis->set($key, $value, $expire);
        }
    }

    /**
     * Delete basis of key
     *
     * @param string $key Cache key
     * @return int
     */
    public function deleteCache($key)
    {
        $this->connect();
        return $this->redis->del($key);
    }
    
    /**
     * Checks member is present in set
     *
     * @param string $set    Cache Set
     * @param string $member Cache Set member
     * @return boolean
     */
    public function isSetMember($set, $member)
    {
        $this->connect();
        return $this->redis->sIsMember($set, $member);
    }

    /**
     * Set Set values
     *
     * @param string $key        Cache Set key
     * @param array  $valueArray Cache values for Set
     * @return void
     */
    public function setSetMembers($key, &$valueArray)
    {
        $this->connect();
        $this->deleteCache($key);
        foreach ($valueArray as $value) {
            $this->redis->sAdd($key, $value);
        }
    }
}
