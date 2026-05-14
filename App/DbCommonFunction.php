<?php

/**
 * Database Common Function
 * php version 8.3
 *
 * @category  Database Common Function
 * @package   Microservices
 * @author    Ramesh N. Jangid (Sharma) <polygon.co.in@gmail.com>
 * @copyright © 2026 Ramesh N. Jangid (Sharma)
 * @license   MIT https://opensource.org/license/mit
 * @link      https://github.com/polygoncoin/Microservices
 * @since     Class available since Release 1.0.0
 */

namespace Microservices\App;

use Microservices\App\Env;
use Microservices\App\HttpStatus;
use Microservices\App\Server\CacheServer;
use Microservices\App\Server\DatabaseServer;
use Microservices\App\Server\QueryCacheServer;
use Microservices\App\Server\CacheServer\CacheServerInterface;
use Microservices\App\Server\DatabaseServer\DatabaseServerInterface;
use Microservices\App\Server\QueryCacheServer\QueryCacheServerInterface;

/**
 * Database Common Function
 * php version 8.3
 *
 * @category  Database Common Function
 * @package   Microservices
 * @author    Ramesh N. Jangid (Sharma) <polygon.co.in@gmail.com>
 * @copyright © 2026 Ramesh N. Jangid (Sharma)
 * @license   MIT https://opensource.org/license/mit
 * @link      https://github.com/polygoncoin/Microservices
 * @since     Class available since Release 1.0.0
 */
class DbCommonFunction
{
	/**
	 * Query Cache Connection Object
	 *
	 * @var null|QueryCacheServerInterface
	 */
	private static $queryCacheServer = null;

	/** Database Connection */
	/**
	 * Global
	 *
	 * @var null|DatabaseServerInterface
	 */
	public static $gDbServer = null;

	/** Cache Connection */
	/**
	 * Global
	 *
	 * @var null|CacheServerInterface
	 */
	public static $gCacheServer = null;

	/**
	 * Connect Cache
	 *
	 * @param string      $cacheServerType     Cache Server Type
	 * @param string      $cacheServerHostname Cache Server Hostname
	 * @param int         $cacheServerPort     Cache Server Port
	 * @param string      $cacheServerUsername Cache Server Username
	 * @param string      $cacheServerPassword Cache Server Password
	 * @param null|string $cacheServerDb       Cache Server Database
	 * @param null|string $cacheServerTable    Cache Server Table
	 *
	 * @return CacheServerInterface
	 */
	public static function connectCache(
		$cacheServerType,
		$cacheServerHostname,
		$cacheServerPort,
		$cacheServerUsername,
		$cacheServerPassword,
		$cacheServerDb,
		$cacheServerTable
	): CacheServerInterface {
		$cacheServer = new CacheServer(
			cacheServerType: $cacheServerType,
			cacheServerHostname: $cacheServerHostname,
			cacheServerPort: $cacheServerPort,
			cacheServerUsername: $cacheServerUsername,
			cacheServerPassword: $cacheServerPassword,
			cacheServerDb: $cacheServerDb,
			cacheServerTable: $cacheServerTable
		);

		return $cacheServer->connectCache();
	}

	/**
	 * Connect global Cache
	 *
	 * @return void
	 */
	public static function connectGlobalCache(): void
	{
		if (self::$gCacheServer !== null) {
			return;
		}
		self::$gCacheServer = self::connectCache(
			cacheServerType: Env::$gCacheServerType,
			cacheServerHostname: Env::$gCacheServerHostname,
			cacheServerPort: Env::$gCacheServerPort,
			cacheServerUsername: Env::$gCacheServerUsername,
			cacheServerPassword: Env::$gCacheServerPassword,
			cacheServerDb: Env::$gCacheServerDb,
			cacheServerTable: Env::$gCacheServerTable
		);
	}

	/**
	 * Connect client Cache based on $fetchFrom
	 *
	 * @param array $cDetail Customer detail
	 *
	 * @return CacheServerInterface
	 * @throws \Exception
	 */
	public static function connectClientCache(&$cDetail): CacheServerInterface
	{
		$clientCacheDetail = self::clientCacheDetail(cDetail: $cDetail);
		return self::connectCache(
			cacheServerType: $clientCacheDetail['cacheServerType'],
			cacheServerHostname: $clientCacheDetail['cacheServerHostname'],
			cacheServerPort: $clientCacheDetail['cacheServerPort'],
			cacheServerUsername: $clientCacheDetail['cacheServerUsername'],
			cacheServerPassword: $clientCacheDetail['cacheServerPassword'],
			cacheServerDb: $clientCacheDetail['cacheServerDb'],
			cacheServerTable: $clientCacheDetail['cacheServerTable']
		);
	}

	/**
	 * Connect query Cache
	 *
	 * @param string $fetchFrom Master/Slave
	 *
	 * @return void
	 */
	public static function connectQueryCache(): void
	{
		if (self::$queryCacheServer !== null) {
			return;
		}

		$queryCacheServer = new QueryCacheServer(
			queryCacheServerType: Env::$queryCacheServerType,
			queryCacheServerHostname: Env::$queryCacheServerHostname,
			queryCacheServerPort: Env::$queryCacheServerPort,
			queryCacheServerUsername: Env::$queryCacheServerUsername,
			queryCacheServerPassword: Env::$queryCacheServerPassword,
			queryCacheServerDb: Env::$queryCacheServerDb,
			queryCacheServerTable: Env::$queryCacheServerTable
		);

		self::$queryCacheServer = $queryCacheServer->connectQueryCache();
	}

	/**
	 * Connect Database
	 *
	 * @param string      $dbServerType     Database Server Type
	 * @param string      $dbServerHostname Database Server Hostname
	 * @param int         $dbServerPort     Database Server Port
	 * @param string      $dbServerUsername Database Server Username
	 * @param string      $dbServerPassword Database Server Password
	 * @param null|string $dbServerDb       Database Server Database
	 *
	 * @return DatabaseServerInterface
	 */
	public static function connectDb(
		$dbServerType,
		$dbServerHostname,
		$dbServerPort,
		$dbServerUsername,
		$dbServerPassword,
		$dbServerDb
	): DatabaseServerInterface {
		$dbServer = new DatabaseServer(
			dbServerType: $dbServerType,
			dbServerHostname: $dbServerHostname,
			dbServerPort: $dbServerPort,
			dbServerUsername: $dbServerUsername,
			dbServerPassword: $dbServerPassword,
			dbServerDb: $dbServerDb
		);

		return $dbServer->connectDb();
	}

	/**
	 * Connect global Database
	 *
	 * @return void
	 */
	public static function connectGlobalDb(): void
	{
		if (self::$gDbServer !== null) {
			return;
		}
		self::$gDbServer = self::connectDb(
			dbServerType: Env::$gDbServerType,
			dbServerHostname: Env::$gDbServerHostname,
			dbServerPort: Env::$gDbServerPort,
			dbServerUsername: Env::$gDbServerUsername,
			dbServerPassword: Env::$gDbServerPassword,
			dbServerDb: Env::$gDbServerDb
		);
	}

	/**
	 * Connect client Database based on $fetchFrom
	 *
	 * @param array  $cDetail Customer detail
	 * @param string $fetchFrom Master/Slave
	 *
	 * @return DatabaseServerInterface
	 * @throws \Exception
	 */
	public static function connectClientDb(&$cDetail, $fetchFrom): DatabaseServerInterface
	{
		// Set Database credentials
		switch ($fetchFrom) {
			case 'Master':
				$clientDbMasterDetail = self::clientDbMasterDetail(cDetail: $cDetail);
				return self::connectDb(
					dbServerType: $clientDbMasterDetail['dbServerType'],
					dbServerHostname: $clientDbMasterDetail['dbServerHostname'],
					dbServerPort: $clientDbMasterDetail['dbServerPort'],
					dbServerUsername: $clientDbMasterDetail['dbServerUsername'],
					dbServerPassword: $clientDbMasterDetail['dbServerPassword'],
					dbServerDb: $clientDbMasterDetail['dbServerDb']
				);
				break;
			case 'Slave':
				$dbSlaveDetail = self::dbSlaveDetail(cDetail: $cDetail);
				return self::connectDb(
					dbServerType: $dbSlaveDetail['dbServerType'],
					dbServerHostname: $dbSlaveDetail['dbServerHostname'],
					dbServerPort: $dbSlaveDetail['dbServerPort'],
					dbServerUsername: $dbSlaveDetail['dbServerUsername'],
					dbServerPassword: $dbSlaveDetail['dbServerPassword'],
					dbServerDb: $dbSlaveDetail['dbServerDb']
				);
				break;
			default:
				throw new \Exception(
					message: "Invalid fetchFrom value '{$fetchFrom}'",
					code: HttpStatus::$InternalServerError
				);
		}
	}

	/**
	 * Prepend Query Cache key
	 *
	 * @param int    $cID           Customer id
	 * @param string $queryCacheKey Query Cache key
	 *
	 * @return string
	 */
	public static function queryCachePrepend($cID, $queryCacheKey): string
	{
		return "qc:{$cID}:{$queryCacheKey}";
	}

	/**
	 * Get Query Cache key
	 *
	 * @param int    $cID           Customer id
	 * @param string $queryCacheKey Query Cache key
	 *
	 * @return mixed
	 */
	public static function queryCacheGet($cID, $queryCacheKey): mixed
	{
		self::connectQueryCache();

		$queryCacheKey = self::queryCachePrepend(
			cID: $cID,
			queryCacheKey: $queryCacheKey
		);

		$json = null;
		if (self::$queryCacheServer->queryCacheExist(queryCacheKey: $queryCacheKey)) {
			$json = self::$queryCacheServer->queryCacheGet(queryCacheKey: $queryCacheKey);
		}

		return $json;
	}

	/**
	 * Increment Query Cache key counter
	 *
	 * @param int    $cID           Customer id
	 * @param string $queryCacheKey Query Cache key
	 *
	 * @return int
	 */
	public static function queryCacheIncrement($cID, $queryCacheKey): int
	{
		self::connectQueryCache();

		$queryCacheKey = self::queryCachePrepend(
			cID: $cID,
			queryCacheKey: $queryCacheKey
		);

		return self::$queryCacheServer->queryCacheIncrement(queryCacheKey: 'i:' . $queryCacheKey);
	}

	/**
	 * Set Query Cache key
	 *
	 * @param int    $cID           Customer id
	 * @param string $queryCacheKey Query Cache key
	 * @param string $json          JSON
	 *
	 * @return void
	 */
	public static function queryCacheSet($cID, $queryCacheKey, &$json): void
	{
		self::connectQueryCache();

		$queryCacheKey = self::queryCachePrepend(
			cID: $cID,
			queryCacheKey: $queryCacheKey
		);

		self::$queryCacheServer->queryCacheSet(queryCacheKey: $queryCacheKey, value: $json);
		self::$queryCacheServer->queryCacheDelete(queryCacheKey: 'i:' . $queryCacheKey);
	}

	/**
	 * Delete Query Cache key
	 *
	 * @param int    $cID           Customer id
	 * @param string $queryCacheKey Query Cache key
	 *
	 * @return void
	 */
	public static function queryCacheDelete($cID, $queryCacheKey): void
	{
		self::connectQueryCache();

		$queryCacheKey = self::queryCachePrepend(
			cID: $cID,
			queryCacheKey: $queryCacheKey
		);

		self::$queryCacheServer->queryCacheDelete(queryCacheKey: $queryCacheKey);
	}

	/**
	 * Returns Cache Master Server detail
	 *
	 * @param array $cDetail Customer detail
	 *
	 * @return array
	 */
	public static function clientCacheDetail(&$cDetail): array
	{
		return [
			'cacheServerType' => getenv(name: $cDetail['cache_server_type']),
			'cacheServerHostname' => getenv(name: $cDetail['cache_server_hostname']),
			'cacheServerPort' => getenv(name: $cDetail['cache_server_port']),
			'cacheServerUsername' => getenv(name: $cDetail['cache_server_username']),
			'cacheServerPassword' => getenv(name: $cDetail['cache_server_password']),
			'cacheServerDb' => getenv(name: $cDetail['cache_server_db']),
			'cacheServerTable' => getenv(name: $cDetail['cache_server_table'])
		];
	}

	/**
	 * Returns Database Master Server detail
	 *
	 * @param array $cDetail Customer detail
	 *
	 * @return array
	 */
	public static function clientDbMasterDetail(&$cDetail): array
	{
		return [
			'dbServerType' => getenv(name: $cDetail['master_db_server_type']),
			'dbServerHostname' => getenv(name: $cDetail['master_db_server_hostname']),
			'dbServerPort' => getenv(name: $cDetail['master_db_server_port']),
			'dbServerUsername' => getenv(name: $cDetail['master_db_server_username']),
			'dbServerPassword' => getenv(name: $cDetail['master_db_server_password']),
			'dbServerDb' => getenv(name: $cDetail['master_db_server_db']),
		];
	}

	/**
	 * Returns Database Slave Server detail
	 *
	 * @param array $cDetail Customer detail
	 *
	 * @return array
	 */
	public static function dbSlaveDetail(&$cDetail): array
	{
		return [
			'dbServerType' => getenv(name: $cDetail['slave_db_server_type']),
			'dbServerHostname' => getenv(name: $cDetail['slave_db_server_hostname']),
			'dbServerPort' => getenv(name: $cDetail['slave_db_server_port']),
			'dbServerUsername' => getenv(name: $cDetail['slave_db_server_username']),
			'dbServerPassword' => getenv(name: $cDetail['slave_db_server_password']),
			'dbServerDb' => getenv(name: $cDetail['slave_db_server_db']),
		];
	}
}
