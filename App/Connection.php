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
