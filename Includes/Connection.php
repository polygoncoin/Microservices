<?php
namespace Includes;

use Includes\Servers\Cache;
use Includes\Servers\Database;
/*
MIT License 

Copyright (c) 2023 Ramesh Narayan Jangid. 

Permission is hereby granted, free of charge, to any person obtaining a copy 
of this software and associated documentation files (the "Software"), to deal 
in the Software without restriction, including without limitation the rights 
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell 
copies of the Software, and to permit persons to whom the Software is 
furnished to do so, subject to the following conditions: 

The above copyright notice and this permission notice shall be included in all 
copies or substantial portions of the Software. 

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR 
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, 
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE 
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER 
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, 
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE 
SOFTWARE. 
*/
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
