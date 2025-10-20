<?php

/**
 * Rate Limiter
 * php version 8.3
 *
 * @category  RateLimiter
 * @package   Microservices
 * @author    Ramesh N Jangid <polygon.co.in@gmail.com>
 * @copyright 2025 Ramesh N Jangid
 * @license   MIT https://opensource.org/license/mit
 * @link      https://github.com/polygoncoin/Microservices
 * @since     Class available since Release 1.0.0
 */

namespace Microservices\App;

use Microservices\App\Servers\Containers\NoSql\AbstractCache;

/**
 * Rate Limiter
 * php version 8.3
 *
 * @category  RateLimiter
 * @package   Microservices
 * @author    Ramesh N Jangid <polygon.co.in@gmail.com>
 * @copyright 2025 Ramesh N Jangid
 * @license   MIT https://opensource.org/license/mit
 * @link      https://github.com/polygoncoin/Microservices
 * @since     Class available since Release 1.0.0
 */
class RateLimiter
{
    /**
     * Caching object
     *
     * @var null|AbstractCache
     */
    private $cache = null;

    /**
     * Current timestamp
     *
     * @var null|int
     */
    private $currentTimestamp = null;

    /**
     * Constructor
     *
     * @throws \Exception
     */
    public function __construct()
    {
        $rateLimitHostType = getenv(name: 'rateLimitHostType');
        $rateLimitHost = getenv(name: 'rateLimitHost');
        $rateLimitHostPort = getenv(name: 'rateLimitHostPort');

        $this->cache = $this->connectCache(
            cacheType: $rateLimitHostType,
            cacheHostname: $rateLimitHost,
            cachePort: $rateLimitHostPort
        );

        $this->currentTimestamp = time();
    }

    /**
     * Check the request is valid
     *
     * @param string $prefix        Prefix
     * @param int    $maxRequests   Max request
     * @param int    $secondsWindow Window in seconds
     * @param string $key           Key
     *
     * @return array
     */
    public function check(
        $prefix,
        $maxRequests,
        $secondsWindow,
        $key
    ): array {
        $maxRequests = (int)$maxRequests;
        $secondsWindow = (int)$secondsWindow;

        $remainder = $this->currentTimestamp % $secondsWindow;
        $remainder = $remainder !== 0 ? $remainder : $secondsWindow;

        $key = $prefix . $key;

        if ($this->cache->cacheExists($key)) {
            $requestCount = (int)$this->cache->getCache($key);
        } else {
            $requestCount = 0;
            $this->cache->setCache($key, $requestCount, $remainder);
        }
        $requestCount++;

        $allowed = $requestCount <= $maxRequests;
        $remaining = max(0, $maxRequests - $requestCount);
        $resetAt = $this->currentTimestamp + $remainder;

        if ($allowed) {
            $this->cache->incrementCache($key);
        }

        return [
            'allowed' => $allowed,
            'remaining' => $remaining,
            'resetAt' => $resetAt
        ];
    }

    /**
     * Connect and get cache server object
     *
     * @param string $cacheType     Cache type
     * @param string $cacheHostname Hostname
     * @param int    $cachePort     Port
     *
     * @return object
     */
    private function connectCache(
        $cacheType,
        $cacheHostname,
        $cachePort
    ): object {
        $cacheNS = 'Microservices\\App\\Servers\\Cache\\' . $cacheType;
        return new $cacheNS(
            $cacheHostname,
            $cachePort
        );
    }
}
