<?php
namespace App;

use App\Servers\Cache;
use App\Servers\Database;

/**
 * Class maintaining connection for cache and database.
 *
 * This class is built to handle object of cache and database server.
 *
 * @category   Cache
 * @package    Microservices
 * @author     Ramesh Narayan Jangid
 * @copyright  Ramesh Narayan Jangid
 * @license    http://www.php.net/license/3_01.txt  PHP License 3.01
 * @version    Release: @1.0.0@
 * @since      Class available since Release 1.0.0
 */
class Connection
{
    /**
     * Cache ojject
     *
     * @var object
     */
    public $cache = null;

    /**
     * Database ojject
     *
     * @var object
     */
    public $db = null;

    /**
     * Constructor initiating Cache and Database objects
     */
    function __construct()
    {
        $this->cache = new Cache();
        $this->db = new Database();
    }

    /**
     * Checks if cache key exist
     *
     * @param string $key Cache key
     * @return boolean
     */
    public function cacheExists($key)
    {
        $this->cache->redis->connect();
        return $this->cache->redis->exists($key);
    }

    /**
     * Get cache on basis of key
     *
     * @param string $key Cache key
     * @return string
     */
    public function getCache($key)
    {
        $this->cache->redis->connect();
        return $this->cache->redis->get($key);
    }

    /**
     * Set cache on basis of key
     *
     * @param string $key    Cache key
     * @param string $value  Cache value
     * @param int    $expire Seconds to expire. Default 0 - doesnt expire
     * @return int
     */
    public function setCache($key, $value, $expire = 0)
    {
        $this->cache->redis->connect();
        return $this->cache->redis->set($key, $value);
    }

    /**
     * Delete basis of key
     *
     * @param string $key Cache key
     * @return int
     */
    public function deleteCache($key)
    {
        $this->cache->redis->connect();
        return $redis->cache->redis->delete($key);
    }
    
    /**
     * Checks member is present in set
     *
     * @param string $set    Cache Set
     * @param string $member Cache Set member
     * @return bool
     */
    public function isSetMember($set, $member)
    {
        $this->cache->redis->connect();
        return $this->cache->redis->sIsMember($set, $member);
    }

    /**
     * Set Set values
     *
     * @param string $key        Cache Set key
     * @param array  $valueArray Cache values for Set
     * @return void
     */
    public function setSetMembers($key, $valueArray)
    {
        $this->cache->redis->connect();
        $this->deleteCache($key);
        foreach ($valueArray as $value) {
            $this->cache->redis->sAdd($key, $value);
        }
    }

    /**
     * Prepare select SQL and return statement object
     *
     * @param string $sql SQL statement
     * @return object
     */
    public function select($sql)
    {
        $this->db->pdo->connect();
        return $this->db->pdo->prepare($sql, [PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY]);
    }

    /**
     * Prepare insert SQL and return statement object
     *
     * @param string $sql SQL statement
     * @return object
     */
    public function insert($sql)
    {
        $this->db->pdo->connect();
        return $this->db->pdo->prepare($sql, [PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY]);
    }

    /**
     * Prepare update SQL and return statement object
     *
     * @param string $sql SQL statement
     * @return object
     */
    public function update($sql)
    {
        $this->db->pdo->connect();
        return $this->db->pdo->prepare($sql, [PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY]);
    }
}
