<?php
namespace App;

use App\HttpRequest;
use App\HttpErrorResponse;
use App\Servers\Cache\Cache;
use App\Servers\Database\Database;
use App\Logs;

/**
 * Class handles Authorization
 *
 * This class is built to process and handle Authorization
 *
 * @category   Authorize
 * @package    Microservices
 * @author     Ramesh Narayan Jangid
 * @copyright  Ramesh Narayan Jangid
 * @version    Release: @1.0.0@
 * @since      Class available since Release 1.0.0
 */
class Authorize
{
    /**
     * Cache Server connection object
     *
     * @var object
     */
    public $cache = null;

    /**
     * DB Server connection object
     *
     * @var object
     */
    public $db = null;

    /**
     * Read only Session
     *
     * @var array
     */
    public $readOnlySession = null;

    /**
     * Client database server type
     *
     * @var string
     */
    public $clientServerType = null;

    /**
     * Client database hostname
     *
     * @var string
     */
    public $clientHostname = null;

    /**
     * Client database username
     *
     * @var string
     */
    public $clientUsername = null;

    /**
     * Client database password
     *
     * @var string
     */
    public $clientPassword = null;

    /**
     * Client database
     *
     * @var string
     */
    public $clientDatabase = null;

    /**
     * Logged-in User ID
     *
     * @var int
     */
    public $userId = null;

    /**
     * Logged-in user Group ID
     *
     * @var int
     */
    public $groupId = null;

    /**
     * Constructor
     *
     * @return void
     */
    public function __construct()
    {
        HttpRequest::init();
        $this->process();
    }

    /**
     * Process authorization
     *
     * @return void
     */
    private function process()
    {
        Cache::connect(
            'Redis',
            'cacheHostname',
            'cachePort',
            'cachePassword',
            'cacheDatabase'
        );
        $this->cache = Cache::getObject();
        if ($this->tokenExists(HttpRequest::$input['token'])) {
            HttpRequest::parseRoute();
            $this->loadTokenSession(HttpRequest::$input['token']);
            $this->checkRemoteIp(HttpRequest::$REMOTE_ADDR);
            $this->checkRoutePrivilage(HttpRequest::$configuredUri);
        } else {
            HttpErrorResponse::return4xx(404, 'Token expired');
        }
    }

    /**
     * Check token exist.
     *
     * @param string $token Bearer token
     * @return void
     */
    private function tokenExists($token)
    {
        return $this->cache->cacheExists($token);
    }

    /**
     * Load session with help of token
     *
     * @param string $token Bearer token
     * @return void
     */
    private function loadTokenSession($token)
    {
        if (!$this->cache->cacheExists($token)) {
            HttpErrorResponse::return5xx(501, "Cache token missing.");
        }
        HttpRequest::$input['readOnlySession'] = json_decode($this->cache->getCache($token), true);

        if (empty(HttpRequest::$input['readOnlySession']['user_id']) || empty(HttpRequest::$input['readOnlySession']['group_id'])) {
            HttpErrorResponse::return4xx(404, 'Invalid session');
        }
        $this->userId = HttpRequest::$input['readOnlySession']['user_id'];
        $this->groupId = HttpRequest::$input['readOnlySession']['group_id'];
        if (!$this->cache->cacheExists("group:{$this->groupId}")) {
            HttpErrorResponse::return5xx(501, "Cache 'group:{$this->groupId}' missing.");
        }
        $groupInfoArr = json_decode($this->cache->getCache("group:{$this->groupId}"), true);
        $this->clientServerType = $groupInfoArr['db_server_type'];
        $this->clientHostname = $groupInfoArr['db_hostname'];
        $this->clientUsername = $groupInfoArr['db_username'];
        $this->clientPassword = $groupInfoArr['db_password'];
        $this->clientDatabase = $groupInfoArr['db_database'];
    }

    /**
     * Validate request IP
     *
     * @param string $ip IP Address (V4).
     * @return void
     */
    private function checkRemoteIp($ip)
    {
        // Redis - one can find the userID from username.
        if ($this->cache->cacheExists("group:{$this->groupId}:cidr")) {
            $cidrs = json_decode($this->cache->getCache("group:{$this->groupId}:cidr"), true);
            $isValidIp = false;
            foreach ($cidrs as $cidr) {
                if (cidr_match($ip, $cidr)) {
                    $isValidIp = true;
                    break;
                }
            }
            if (!$isValidIp) {
                HttpErrorResponse::return4xx(404, 'Invalid request.');
            }
        }
    }

    /**
     * Check Requested route privilage
     *
     * @param int    $groupId Group ID
     * @param string $route   Raw route configured in Routes folder.
     * @return void
     */
    private function checkRoutePrivilage($route)
    {
        $key = "group:{$this->groupId}:http:".HttpRequest::$httpId.':routes';
        if (!$this->cache->isSetMember($key, $route)) {
            HttpErrorResponse::return4xx(404, 'Route not supported');
        }
    }
    
    /**
     * Function to connect to client database
     *
     * @return void
     */
    public function connectClientDB()
    {
        if (!is_null($this->db)) return;
        Database::connect(
            $this->clientServerType,
            $this->clientHostname,
            $this->clientUsername,
            $this->clientPassword,
            $this->clientDatabase
        );
        $this->db = Database::getObject();
    }

    /**
     * Destructor
     */
    public function __destruct()
    {
        
    }
}
