<?php

/**
 * DB Functions
 * php version 8.3
 *
 * @category  DbFunctions
 * @package   Microservices
 * @author    Ramesh N Jangid <polygon.co.in@gmail.com>
 * @copyright 2025 Ramesh N Jangid
 * @license   MIT https://opensource.org/license/mit
 * @link      https://github.com/polygoncoin/Microservices
 * @since     Class available since Release 1.0.0
 */

namespace Microservices\App;

use Microservices\App\DatabaseCacheKey;
use Microservices\App\DatabaseOpenCacheKey;
use Microservices\App\HttpRequest;
use Microservices\App\HttpStatus;
use Microservices\App\Servers\Cache\AbstractCache;

/**
 * DB Functions
 * php version 8.3
 *
 * @category  DbFunctions
 * @package   Microservices
 * @author    Ramesh N Jangid <polygon.co.in@gmail.com>
 * @copyright 2025 Ramesh N Jangid
 * @license   MIT https://opensource.org/license/mit
 * @link      https://github.com/polygoncoin/Microservices
 * @since     Class available since Release 1.0.0
 */
class DbFunctions
{
    /**
     * Rate Limiter
     *
     * @var null|HttpRequest
     */
    private $req = null;

    /**
     * Constructor
     *
     * @param HttpRequest $req HTTP Request object
     */
    public function __construct(&$req)
    {
        $this->req = &$req;
    }

    /**
     * Set Cache
     *
     * @param string $globalCacheType     Cache type
     * @param string $globalCacheHostname Hostname
     * @param int    $globalCachePort     Port
     * @param string $globalCacheUsername Username
     * @param string $globalCachePassword Password
     * @param string $globalCacheDatabase Database
     *
     * @return object
     */
    public function connectCache(
        $globalCacheType,
        $globalCacheHostname,
        $globalCachePort,
        $globalCacheUsername,
        $globalCachePassword,
        $globalCacheDatabase
    ): object {
        $cacheNS = 'Microservices\\App\\Servers\\Cache\\' . $globalCacheType;
        return new $cacheNS(
            $globalCacheHostname,
            $globalCachePort,
            $globalCacheUsername,
            $globalCachePassword,
            $globalCacheDatabase
        );
    }

    /**
     * Init server connection based on $fetchFrom
     *
     * @param string $fetchFrom Master/Slave
     *
     * @return object
     * @throws \Exception
     */
    public function setCacheConnection($fetchFrom): object
    {
        if ($this->req->s['cDetails'] === null) {
            throw new \Exception(
                message: 'Yet to set connection params',
                code: HttpStatus::$InternalServerError
            );
        }

        // Set Database credentials
        switch ($fetchFrom) {
            case 'Master':
                return $this->connectCache(
                    globalCacheType: getenv(
                        name: $this->req->s['cDetails']['master_cache_server_type']
                    ),
                    globalCacheHostname: getenv(
                        name: $this->req->s['cDetails']['master_cache_hostname']
                    ),
                    globalCachePort: getenv(
                        name: $this->req->s['cDetails']['master_cache_port']
                    ),
                    globalCacheUsername: getenv(
                        name: $this->req->s['cDetails']['master_cache_username']
                    ),
                    globalCachePassword: getenv(
                        name: $this->req->s['cDetails']['master_cache_password']
                    ),
                    globalCacheDatabase: getenv(
                        name: $this->req->s['cDetails']['master_cache_database']
                    )
                );
            case 'Slave':
                return $this->connectCache(
                    globalCacheType: getenv(
                        name: $this->req->s['cDetails']['slave_cache_server_type']
                    ),
                    globalCacheHostname: getenv(
                        name: $this->req->s['cDetails']['slave_cache_hostname']
                    ),
                    globalCachePort: getenv(
                        name: $this->req->s['cDetails']['slave_cache_port']
                    ),
                    globalCacheUsername: getenv(
                        name: $this->req->s['cDetails']['slave_cache_username']
                    ),
                    globalCachePassword: getenv(
                        name: $this->req->s['cDetails']['slave_cache_password']
                    ),
                    globalCacheDatabase: getenv(
                        name: $this->req->s['cDetails']['slave_cache_database']
                    )
                );
            default:
                throw new \Exception(
                    message: "Invalid fetchFrom value '{$fetchFrom}'",
                    code: HttpStatus::$InternalServerError
                );
        }
    }

    /**
     * Set DB
     *
     * @param string $dbType     Cache type
     * @param string $dbHostname Hostname
     * @param int    $dbPort     Port
     * @param string $dbUsername Username
     * @param string $dbPassword Password
     * @param string $dbDatabase Database
     *
     * @return object
     */
    public function connectDb(
        $dbType,
        $dbHostname,
        $dbPort,
        $dbUsername,
        $dbPassword,
        $dbDatabase
    ): object {
        $dbNS = 'Microservices\\App\\Servers\\Database\\' . $dbType;
        return new $dbNS(
            $dbHostname,
            $dbPort,
            $dbUsername,
            $dbPassword,
            $dbDatabase
        );
    }

    /**
     * Init server connection based on $fetchFrom
     *
     * @param string $fetchFrom Master/Slave
     *
     * @return object
     * @throws \Exception
     */
    public function setDbConnection($fetchFrom): object
    {
        if ($this->req->s['cDetails'] === null) {
            throw new \Exception(
                message: 'Yet to set connection params',
                code: HttpStatus::$InternalServerError
            );
        }

        // Set Database credentials
        switch ($fetchFrom) {
            case 'Master':
                return $this->connectDb(
                    dbType: getenv(
                        name: $this->req->s['cDetails']['master_db_server_type']
                    ),
                    dbHostname: getenv(
                        name: $this->req->s['cDetails']['master_db_hostname']
                    ),
                    dbPort: getenv(
                        name: $this->req->s['cDetails']['master_db_port']
                    ),
                    dbUsername: getenv(
                        name: $this->req->s['cDetails']['master_db_username']
                    ),
                    dbPassword: getenv(
                        name: $this->req->s['cDetails']['master_db_password']
                    ),
                    dbDatabase: getenv(
                        name: $this->req->s['cDetails']['master_db_database']
                    )
                );
            case 'Slave':
                return $this->connectDb(
                    dbType: getenv(
                        name: $this->req->s['cDetails']['slave_db_server_type']
                    ),
                    dbHostname: getenv(
                        name: $this->req->s['cDetails']['slave_db_hostname']
                    ),
                    dbPort: getenv(
                        name: $this->req->s['cDetails']['slave_db_port']
                    ),
                    dbUsername: getenv(
                        name: $this->req->s['cDetails']['slave_db_username']
                    ),
                    dbPassword: getenv(
                        name: $this->req->s['cDetails']['slave_db_password']
                    ),
                    dbDatabase: getenv(
                        name: $this->req->s['cDetails']['slave_db_database']
                    )
                );
            default:
                throw new \Exception(
                    message: "Invalid fetchFrom value '{$fetchFrom}'",
                    code: HttpStatus::$InternalServerError
                );
        }
    }

    /**
     * Set Cache prefix key
     *
     * @return void
     */
    public function setDatabaseCacheKey(): void
    {
        if ($this->req->open) {
            DatabaseOpenCacheKey::init(cID: $this->req->s['cDetails']['id']);
        } else {
            DatabaseCacheKey::init(
                cID: $this->req->s['cDetails']['id'],
                gID: $this->req->s['gDetails']['id'],
                uID: $this->req->s['uDetails']['id']
            );
        }
    }

    /**
     * Get Query cache
     *
     * @param string $cacheKey Cache Key from Queries configuration
     *
     * @return mixed
     */
    public function getQueryCache($cacheKey): mixed
    {
        if ($this->req->sqlCache === null) {
            $this->req->sqlCache = $this->setCacheConnection(fetchFrom: 'Slave');
        }

        if ($this->req->sqlCache->cacheExists(key: $cacheKey)) {
            return $json = $this->req->sqlCache->getCache(key: $cacheKey);
        } else {
            return $json = null;
        }
    }

    /**
     * Set Query cache
     *
     * @param string $cacheKey Cache Key from Queries configuration
     * @param string $json     JSON
     *
     * @return void
     */
    public function setQueryCache($cacheKey, &$json): void
    {
        if ($this->req->sqlCache === null) {
            $this->req->sqlCache = $this->setCacheConnection(fetchFrom: 'Master');
        }

        $this->req->sqlCache->setCache(key: $cacheKey, value: $json);
    }

    /**
     * Delete Query Cache
     *
     * @param string $cacheKey Cache Key from Queries configuration
     *
     * @return void
     */
    public function delQueryCache($cacheKey): void
    {
        if ($this->req->sqlCache === null) {
            $this->req->sqlCache = $this->setCacheConnection(fetchFrom: 'Master');
        }

        $this->req->sqlCache->deleteCache(key: $cacheKey);
    }
}
