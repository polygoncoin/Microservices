<?php
namespace Microservices\App;

use Microservices\App\RateLimiter;

/**
 * Constants
 *
 * Contains all constants related to Microservices
 *
 * @category   Constants
 * @package    Microservices
 * @author     Ramesh Narayan Jangid
 * @copyright  Ramesh Narayan Jangid
 * @version    Release: @1.0.0@
 * @since      Class available since Release 1.0.0
 */
class Env
{
    static public $globalDatabase = null;
    static public $cacheDatabase = null;

    static public $ENVIRONMENT = null;
    static public $OUTPUT_PERFORMANCE_STATS = null;

    static public $allowConfigRequest = null;
    static public $configRequestUriKeyword = null;

    static public $groups = null;
    static public $client_users = null;
    static public $clients = null;

    static public $maxPerpage = null;
    static public $defaultPerpage = null;

    static public $isConfigRequest = null;

    static public $allowCronRequest = null;
    static public $cronRequestUriPrefix = null;
    static public $cronRestrictedIp = null;

    static public $allowRoutesRequest = null;
    static public $routesRequestUri = null;

    static public $allowCustomRequest = null;
    static public $customRequestUriPrefix = null;

    static public $allowUploadRequest = null;
    static public $uploadRequestUriPrefix = null;

    static public $allowThirdPartyRequest = null;
    static public $thirdPartyRequestUriPrefix = null;

    static public $allowCacheRequest = null;
    static public $cacheRequestUriPrefix = null;

    static private $initialized = null;

    static public function init()
    {
        if (!is_null(self::$initialized)) return;

        // Load .env
        $env = parse_ini_file(__DIR__ . '/../.env');
        foreach ($env as $key => $value) {
            putenv("{$key}={$value}");
        }

        self::$globalDatabase = getenv('globalDatabase');
        self::$cacheDatabase = getenv('cacheDatabase');

        self::$ENVIRONMENT = getenv('ENVIRONMENT');
        self::$OUTPUT_PERFORMANCE_STATS = getenv('OUTPUT_PERFORMANCE_STATS');

        self::$allowConfigRequest = getenv('allowConfigRequest');
        self::$configRequestUriKeyword = getenv('configRequestUriKeyword');

        self::$groups = getenv('groups');
        self::$client_users = getenv('client_users');
        self::$clients = getenv('clients');

        self::$maxPerpage = getenv('maxPerpage');
        self::$defaultPerpage = getenv('defaultPerpage');

        self::$allowCronRequest = getenv('allowCronRequest');
        self::$cronRequestUriPrefix = getenv('cronRequestUriPrefix');
        self::$cronRestrictedIp = getenv('cronRestrictedIp');

        self::$allowRoutesRequest = getenv('allowRoutesRequest');
        self::$routesRequestUri = getenv('routesRequestUri');

        self::$allowCustomRequest = getenv('allowCustomRequest');
        self::$customRequestUriPrefix = getenv('customRequestUriPrefix');

        self::$allowUploadRequest = getenv('allowUploadRequest');
        self::$uploadRequestUriPrefix = getenv('uploadRequestUriPrefix');

        self::$allowThirdPartyRequest = getenv('allowThirdPartyRequest');
        self::$thirdPartyRequestUriPrefix = getenv('thirdPartyRequestUriPrefix');

        self::$allowCacheRequest = getenv('allowCacheRequest');
        self::$cacheRequestUriPrefix = getenv('cacheRequestUriPrefix');

        self::$initialized = true;
    }

    /**
     * Check Rate Limit
     *
     * @return void
     */
    static public function checkRateLimit(
        $RateLimiterHost,
        $RateLimiterHostPort,
        $RateLimiterPrefix,
        $RateLimiterMaxRequests,
        $RateLimiterSecondsWindow,
        $key
    ) {
        try {
            $rateLimiter = new RateLimiter(
                $RateLimiterHost,
                $RateLimiterHostPort,
                $RateLimiterPrefix,
                $RateLimiterMaxRequests,
                $RateLimiterSecondsWindow
            );
        
            $result = $rateLimiter->check($key);
        
            if ($result['allowed']) {
                // Process the request
                return;
            } else {
                // Return 429 Too Many Requests
                http_response_code(429);
                header('Retry-After: ' . ($result['resetAt'] - time()));
                die(json_encode([
                    'error' => 'Too Many Requests',
                    'retryAfter' => $result['resetAt']
                ]));
            }
        
        } catch (Exception $e) {
            // Handle connection errors
            die('Rate limiter error: ' . $e->getMessage());
        }
    }
}
