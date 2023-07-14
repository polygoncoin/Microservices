<?php
namespace App\Servers\Cache;

/**
 * Loading database server
 *
 * This abstract class is built to handle the database server.
 *
 * @category   Abstract Database Class
 * @package    Microservices
 * @author     Ramesh Narayan Jangid
 * @copyright  Ramesh Narayan Jangid
 * @version    Release: @1.0.0@
 * @since      Class available since Release 1.0.0
 */
abstract class AbstractCache
{
    /**
     * Database connection
     *
     * @return void
     */
    abstract public function connect();

    /**
     * Use Database
     *
     * @param string $database Database .env string
     * @return void
     */
    abstract public function useDatabase($database);

    /**
     * Checks if cache key exist
     *
     * @param string $key Cache key
     * @return boolean
     */
    abstract public function cacheExists($key);

    /**
     * Get cache on basis of key
     *
     * @param string $key Cache key
     * @return string
     */
    abstract public function getCache($key);

    /**
     * Set cache on basis of key
     *
     * @param string $key    Cache key
     * @param string $value  Cache value
     * @param int    $expire Seconds to expire. Default 0 - doesnt expire
     * @return int
     */
    abstract public function setCache($key, $value, $expire = null);

    /**
     * Delete basis of key
     *
     * @param string $key Cache key
     * @return int
     */
    abstract public function deleteCache($key);
    
    /**
     * Checks member is present in set
     *
     * @param string $set    Cache Set
     * @param string $member Cache Set member
     * @return boolean
     */
    abstract public function isSetMember($set, $member);

    /**
     * Set Set values
     *
     * @param string $key        Cache Set key
     * @param array  $valueArray Cache values for Set
     * @return void
     */
    abstract public function setSetMembers($key, $valueArray);
}
