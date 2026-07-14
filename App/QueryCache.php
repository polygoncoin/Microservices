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

use Microservices\App\DbCommonFunction;
use Microservices\App\Server\QueryCacheServer;

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
class QueryCache
{
	/**
	 * HTTP object
	 *
	 * @var null|Http
	 */
	private $httpObj = null;

	/**
	 * Query Cache Connection Object
	 *
	 * @var null|QueryCacheServer
	 */
	private $queryCacheServerObj = null;

	/**
	 * Constructor
	 *
	 * @param Http $httpObj
	 */
	public function __construct(
		Http &$httpObj
	) {
		$this->httpObj = &$httpObj;
    }

    /**
	 * Connect query Cache
	 *
	 * @return void
	 */
	public function connectCustomerQueryCache(): void
	{
        if ($this->queryCacheServerObj !== null) {
            return;
        }

		$customerQueryCacheServerCred = DbCommonFunction::customerQueryCacheServerCred(
			customerData: $this->httpObj->requestObj->session['customerData']
		);
		$this->queryCacheServerObj = new QueryCacheServer(
			queryCacheServerType: $customerQueryCacheServerCred['cacheServerType'],
			queryCacheServerHostname: $customerQueryCacheServerCred['cacheServerHostname'],
			queryCacheServerPort: $customerQueryCacheServerCred['cacheServerPort'],
			queryCacheServerUsername: $customerQueryCacheServerCred['cacheServerUsername'],
			queryCacheServerPassword: $customerQueryCacheServerCred['cacheServerPassword'],
			queryCacheServerDatabase: $customerQueryCacheServerCred['cacheServerDatabase'],
			queryCacheServerTable: $customerQueryCacheServerCred['cacheServerTable']
		);
	}

	/**
	 * Prepend Query Cache key
	 *
	 * @param int    $customerId    Customer Id
	 * @param string $queryCacheKey Query Cache key
	 *
	 * @return mixed
	 */
	public function queryCachePrepend(
		$customerId,
		$queryCacheKey
	): mixed {
        $this->connectCustomerQueryCache();

		if (
			strlen($customerId) === 0
			|| strlen($queryCacheKey) === 0
		) {
			return false;
		}

		return "qc:{$customerId}:{$queryCacheKey}";
	}

	/**
	 * Get Query Cache key
	 *
	 * @param int    $customerId    Customer Id
	 * @param string $queryCacheKey Query Cache key
	 *
	 * @return mixed
	 */
	public function queryCacheGet(
		$customerId,
		$queryCacheKey
	): mixed {
        $this->connectCustomerQueryCache();

		if (empty($queryCacheKey)) {
			return false;
		}

		$queryCacheKey = $this->queryCachePrepend(
			customerId: $customerId,
			queryCacheKey: $queryCacheKey
		);

		$json = null;
		if (
			$this->queryCacheServerObj->queryCacheExist(
				queryCacheKey: $queryCacheKey
			)
		) {
			$json = $this->queryCacheServerObj->queryCacheGet(
				queryCacheKey: $queryCacheKey
			);
		}

		return $json;
	}

	/**
	 * Increment Query Cache key counter
	 *
	 * @param int    $customerId    Customer Id
	 * @param string $queryCacheKey Query Cache key
	 *
	 * @return mixed
	 */
	public function queryCacheIncrement(
		$customerId,
		$queryCacheKey
	): mixed {
        $this->connectCustomerQueryCache();

		if (empty($queryCacheKey)) {
			return false;
		}

		$queryCacheKey = 'i:' . $queryCacheKey;
		$queryCacheKey = $this->queryCachePrepend(
			customerId: $customerId,
			queryCacheKey: $queryCacheKey
		);

		return $this->queryCacheServerObj->queryCacheIncrement(
			queryCacheKey: $queryCacheKey
		);
	}

	/**
	 * Set Query Cache key
	 *
	 * @param int    $customerId      Customer Id
	 * @param string $queryCacheKey   Query Cache key
	 * @param mixed  $queryCacheValue Query Cache value
	 *
	 * @return mixed
	 */
	public function queryCacheSet(
		$customerId,
		$queryCacheKey,
		&$queryCacheValue
	): mixed {
        $this->connectCustomerQueryCache();

		if (empty($queryCacheKey)) {
			return false;
		}

		$delQueryCacheKey = 'i:' . $queryCacheKey;

		$queryCacheKey = $this->queryCachePrepend(
			customerId: $customerId,
			queryCacheKey: $queryCacheKey
		);

		$delQueryCacheKey = $this->queryCachePrepend(
			customerId: $customerId,
			queryCacheKey: $delQueryCacheKey
		);

		$this->queryCacheServerObj->queryCacheDelete(
			queryCacheKey: $delQueryCacheKey
		);
		return $this->queryCacheServerObj->queryCacheSet(
			queryCacheKey: $queryCacheKey,
			queryCacheValue: $queryCacheValue
		);
	}

	/**
	 * Delete Query Cache key
	 *
	 * @param int    $customerId    Customer Id
	 * @param string $queryCacheKey Query Cache key
	 *
	 * @return mixed
	 */
	public function queryCacheDelete(
		$customerId,
		$queryCacheKey
	): mixed {
        $this->connectCustomerQueryCache();

		if (empty($queryCacheKey)) {
			return false;
		}

		$queryCacheKey = $this->queryCachePrepend(
			customerId: $customerId,
			queryCacheKey: $queryCacheKey
		);

		return $this->queryCacheServerObj->queryCacheDelete(
			queryCacheKey: $queryCacheKey
		);
	}
}
