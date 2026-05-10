<?php

/**
 * Query Cache
 * php version 8.3
 *
 * @category  QueryCache
 * @package   Microservices
 * @author    Ramesh N. Jangid (Sharma) <polygon.co.in@gmail.com>
 * @copyright © 2026 Ramesh N. Jangid (Sharma)
 * @license   MIT https://opensource.org/license/mit
 * @link      https://github.com/polygoncoin/Microservices
 * @since     Class available since Release 1.0.0
 */

namespace Microservices\App\Server\QueryCacheServer;

/**
 * Query Cache Interface
 * php version 8.3
 *
 * @category  Query_Cache_Interface
 * @package   Microservices
 * @author    Ramesh N. Jangid (Sharma) <polygon.co.in@gmail.com>
 * @copyright © 2026 Ramesh N. Jangid (Sharma)
 * @license   MIT https://opensource.org/license/mit
 * @link      https://github.com/polygoncoin/Microservices
 * @since     Class available since Release 1.0.0
 */
interface QueryCacheServerInterface
{
	/**
	 * Query Cache Server Object
	 *
	 * @return void
	 */
	public function connect(): void;

	/**
	 * Checks if cache key exist
	 *
	 * @param string $queryCacheKey Query cache key
	 *
	 * @return mixed
	 */
	public function queryCacheExists($queryCacheKey): mixed;

	/**
	 * Get cache on basis of key
	 *
	 * @param string $queryCacheKey Query cache key
	 *
	 * @return mixed
	 */
	public function queryCacheGet($queryCacheKey): mixed;

	/**
	 * Set cache on basis of key
	 *
	 * @param string $queryCacheKey    Cache key
	 * @param string $value  Cache value
	 *
	 * @return mixed
	 */
	public function queryCacheSet($queryCacheKey, $value): mixed;

	/**
	 * Delete cache on basis of key
	 *
	 * @param string $queryCacheKey Query cache key
	 *
	 * @return mixed
	 */
	public function queryCacheDelete($queryCacheKey): mixed;
}
