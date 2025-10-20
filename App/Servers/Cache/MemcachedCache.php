<?php

/**
 * Handling Cache via Memcached
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

namespace Microservices\App\Servers\Cache;

use Microservices\App\Servers\Cache\CacheInterface;
use Microservices\App\Servers\Containers\NoSql\Memcached as DB_Memcached;

/**
 * Caching via Memcached
 * php version 8.3
 *
 * @category  Cache_Memcached
 * @package   Microservices
 * @author    Ramesh N Jangid <polygon.co.in@gmail.com>
 * @copyright 2025 Ramesh N Jangid
 * @license   MIT https://opensource.org/license/mit
 * @link      https://github.com/polygoncoin/Microservices
 * @since     Class available since Release 1.0.0
 */
class MemcachedCache extends DB_Memcached implements CacheInterface
{
}
