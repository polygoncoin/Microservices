<?php
/**
 * Environment
 * php version 8.3
 *
 * @category  Environment
 * @package   Microservices
 * @author    Ramesh N Jangid <polygon.co.in@gmail.com>
 * @copyright 2025 Ramesh N Jangid
 * @license   MIT https://opensource.org/license/mit
 * @link      https://github.com/polygoncoin/Microservices
 * @since     Class available since Release 1.0.0
 */
namespace Microservices\App;

/**
 * Environment
 * php version 8.3
 *
 * @category  Environment
 * @package   Microservices
 * @author    Ramesh N Jangid <polygon.co.in@gmail.com>
 * @copyright 2025 Ramesh N Jangid
 * @license   MIT https://opensource.org/license/mit
 * @link      https://github.com/polygoncoin/Microservices
 * @since     Class available since Release 1.0.0
 */
class Env
{
    public static $globalDatabase = null;
    public static $cacheDatabase = null;

    public static $ENVIRONMENT = null;
    public static $OUTPUT_PERFORMANCE_STATS = null;

    public static $allowConfigRequest = null;
    public static $configRequestUriKeyword = null;

    public static $groups = null;
    public static $client_users = null;
    public static $clients = null;

    public static $maxPerPage = null;
    public static $defaultPerPage = null;

    public static $allowCronRequest = null;
    public static $cronRequestUriPrefix = null;
    public static $cronRestrictedIp = null;

    public static $allowRoutesRequest = null;
    public static $routesRequestUri = null;

    public static $allowCustomRequest = null;
    public static $customRequestUriPrefix = null;

    public static $allowUploadRequest = null;
    public static $uploadRequestUriPrefix = null;

    public static $allowThirdPartyRequest = null;
    public static $thirdPartyRequestUriPrefix = null;

    public static $allowCacheRequest = null;
    public static $cacheRequestUriPrefix = null;

    public static $inputRepresentation = null;
    public static $outputRepresentation = null;

    /**
     * Initialize
     *
     * @param array $http HTTP request details
     *
     * @return void
     */
    public static function init(&$http): void
    {
        self::$globalDatabase = getenv(name: 'globalDatabase');
        self::$cacheDatabase = getenv(name: 'cacheDatabase');

        self::$ENVIRONMENT = getenv(name: 'ENVIRONMENT');
        self::$OUTPUT_PERFORMANCE_STATS = getenv(name: 'OUTPUT_PERFORMANCE_STATS');

        self::$allowConfigRequest = getenv(name: 'allowConfigRequest');
        self::$configRequestUriKeyword = getenv(name: 'configRequestUriKeyword');

        self::$groups = getenv(name: 'groups');
        self::$client_users = getenv(name: 'client_users');
        self::$clients = getenv(name: 'clients');

        self::$maxPerPage = getenv(name: 'maxPerPage');
        self::$defaultPerPage = getenv(name: 'defaultPerPage');

        self::$allowCronRequest = getenv(name: 'allowCronRequest');
        self::$cronRequestUriPrefix = getenv(name: 'cronRequestUriPrefix');
        self::$cronRestrictedIp = getenv(name: 'cronRestrictedIp');

        self::$allowRoutesRequest = getenv(name: 'allowRoutesRequest');
        self::$routesRequestUri = getenv(name: 'routesRequestUri');

        self::$allowCustomRequest = getenv(name: 'allowCustomRequest');
        self::$customRequestUriPrefix = getenv(name: 'customRequestUriPrefix');

        self::$allowUploadRequest = getenv(name: 'allowUploadRequest');
        self::$uploadRequestUriPrefix = getenv(name: 'uploadRequestUriPrefix');

        self::$allowThirdPartyRequest = getenv(name: 'allowThirdPartyRequest');
        self::$thirdPartyRequestUriPrefix = getenv(
            name: 'thirdPartyRequestUriPrefix'
        );

        self::$allowCacheRequest = getenv(name: 'allowCacheRequest');
        self::$cacheRequestUriPrefix = getenv(name: 'cacheRequestUriPrefix');

        $inputRepresentation = isset($http['get']['inputRepresentation']) ?
            $http['get']['inputRepresentation'] : null;
        $outputRepresentation = isset($http['get']['outputRepresentation']) ?
            $http['get']['outputRepresentation'] : null;

        if (in_array(needle: $inputRepresentation, haystack: ['Json', 'Xml'])) {
            self::$inputRepresentation = $inputRepresentation;
        } else {
            self::$inputRepresentation = getenv(name: 'inputRepresentation');
        }

        if (in_array(needle: $outputRepresentation, haystack: ['Json', 'Xml'])) {
            self::$outputRepresentation = $outputRepresentation;
        } else {
            self::$outputRepresentation = getenv(
                name: 'outputRepresentation'
            );
        }
    }
}
