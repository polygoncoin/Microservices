<?php

/**
 * Handling Cache via MongoDb
 * php version 8.3
 *
 * @category  Cache
 * @package   Microservices
 * @author    Ramesh N Jangid <polygon.co.in@gmail.com>
 * @copyright 2025 Ramesh N Jangid
 * @license   MIT https://opensource.org/license/mit
 * @link      https://github.com/polygoncoin/Microservices
 * @since     Class available since Release 1.0.0
 */

namespace Microservices\App\Servers\Containers\NoSql;

use Microservices\App\HttpStatus;
use Microservices\App\Servers\QueryCache\QueryCacheInterface;

/**
 * Caching via MongoDb
 * php version 8.3
 *
 * @category  Cache_MongoDb
 * @package   Microservices
 * @author    Ramesh N Jangid <polygon.co.in@gmail.com>
 * @copyright 2025 Ramesh N Jangid
 * @license   MIT https://opensource.org/license/mit
 * @link      https://github.com/polygoncoin/Microservices
 * @since     Class available since Release 1.0.0
 */
class MongoDbQueryCache implements QueryCacheInterface
{
    // "mongodb://<username>:<password>@<cluster-url>:<port>/<database-name>
    // ?retryWrites=true&w=majority"
    private $uri = null;

    /**
     * Cache hostname
     *
     * @var null|string
     */
    private $hostname = null;

    /**
     * Cache port
     *
     * @var null|int
     */
    private $port = null;

    /**
     * Cache password
     *
     * @var null|string
     */
    private $username = null;

    /**
     * Cache password
     *
     * @var null|string
     */
    private $password = null;

    /**
     * Cache database
     *
     * @var null|string
     */
    private $database = null;

    /**
     * Cache collection
     *
     * @var null|string
     */
    public $table = 'key_value';

    /**
     * Cache Object
     *
     * @var null|\MongoDB\Client
     */
    private $cache = null;

    /**
     * Database Object
     *
     * @var null|Object
     */
    private $databaseObj = null;

    /**
     * Collection Object
     *
     * @var null|Object
     */
    private $collectionObj = null;

    /**
     * Cache connection
     *
     * @param string $hostname Hostname .env string
     * @param string $port     Port .env string
     * @param string $username Username .env string
     * @param string $password Password .env string
     * @param string $database Database .env string
     * @param string $table    Table .env string
     */
    public function __construct(
        $hostname,
        $port,
        $username,
        $password,
        $database,
        $table
    ) {
        $this->hostname = $hostname;
        $this->port = $port;
        $this->username = $username;
        $this->password = $password;
        $this->database = $database;
        $this->table = $table;
    }

    /**
     * Cache connection
     *
     * @return void
     * @throws \Exception
     */
    public function connect(): void
    {
        if ($this->cache !== null) {
             return;
        }

        try {
            if ($this->uri === null) {
                $UP = '';
                if ($this->username !== null && $this->password !== null) {
                    $UP = "{$this->username}:{$this->password}@";
                }
                $this->uri = 'mongodb://' . $UP .
                    $this->hostname . ':' . $this->port;
            }
            $this->cache = new \MongoDB\Client($this->uri);

            // Select a database
            $this->databaseObj = $this->cache->selectDatabase($this->database);

            // Select a collection
            $this->collectionObj = $this->databaseObj->selectCollection($this->table);
        } catch (\Exception $e) {
            throw new \Exception(
                message: $e->getMessage(),
                code: HttpStatus::$InternalServerError
            );
        }
    }

    /**
     * Checks if cache key exist
     *
     * @param string $key Cache key
     *
     * @return mixed
     */
    public function cacheExists($key): mixed
    {
        $this->connect();

        $filter = ['key' => $key];

        if ($document = $this->collection->findOne($filter)) {
            return true;
        }
        return false;
    }

    /**
     * Get cache on basis of key
     *
     * @param string $key Cache key
     *
     * @return mixed
     */
    public function getCache($key): mixed
    {
        $this->connect();

        $filter = ['key' => $key];
        return $this->collection->findOne($filter);
    }

    /**
     * Set cache on basis of key
     *
     * @param string $key    Cache key
     * @param string $value  Cache value
     *
     * @return mixed
     */
    public function setCache($key, $value): mixed
    {
        $this->connect();

        $document = [
            'key' => $key,
            'value' => $value
        ];
        if ($this->collection->insertOne($document)) {
            return true;
        }
        return false;
    }

    /**
     * Delete basis of key
     *
     * @param string $key Cache key
     *
     * @return mixed
     */
    public function deleteCache($key): mixed
    {
        $this->connect();

        $filter = ['key' => $key];
        if ($this->collection->deleteOne($filter)) {
            return true;
        }
        return false;
    }
}
