<?php
namespace App;

use App\HttpRequest;
use App\HttpErrorResponse;
use App\Servers\Cache\Redis;
use App\Servers\Database\Database;

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
class Authorize extends HttpRequest
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
     * Global DB
     *
     * @var string
     */
    public $globalDB = null;

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

    }

    /**
     * Initialize authorization
     *
     * @return void
     */
    public function init()
    {
        $this->process();
    }

    /**
     * Process authorization
     *
     * @return void
     */
    private function process()
    {
        $this->globalDB = getenv('globalDbName');
        $this->cache = new Redis(
            'cacheHostname',
            'cachePort',
            'cachePassword',
            'cacheDatabase'
        );
        $this->checkToken($_SERVER['HTTP_AUTHORIZATION']);
        if ($this->tokenExists($this->token)) {
            $this->parseRoute($_SERVER['REQUEST_METHOD'], __REQUEST_URI__);
            $this->loadTokenSession($this->token);
            $this->checkSourceIp($_SERVER['REMOTE_ADDR']);
            $this->checkRoutePrivilage($this->configuredUri);
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
        $this->readOnlySession = json_decode($this->cache->getCache($token), true);

        if (empty($this->readOnlySession['user_id']) || empty($this->readOnlySession['group_id'])) {
            HttpErrorResponse::return4xx(404, 'Invalid session');
        }
        $this->userId = $this->readOnlySession['user_id'];
        $this->groupId = $this->readOnlySession['group_id'];
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
    private function checkSourceIp($ip)
    {
        $foundIP = null;
        $ipNumber = ip2long($ip);
        if ($this->cache->cacheExists("group:{$this->groupId}:ips")) {
            $foundIP = false;
            foreach(json_decode($this->cache->getCache("group:{$this->groupId}:ips"), true) as list($start, $end)) {
                if ($ipNumber >= $start && $ipNumber <= $end) {
                    $foundIP = true;
                    break;
                }
            }
        }
        if (!is_null($foundIP) && !$foundIP) {
            HttpErrorResponse::return4xx(404, 'IP Address not supported');
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
        $key = "group:{$this->groupId}:http:{$this->httpId}:routes";
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
        $this->db = Database::getDbObject(
            $this->clientServerType,
            $this->clientHostname,
            $this->clientUsername,
            $this->clientPassword,
            $this->clientDatabase
        );
    }

    /**
     * Destructor
     */
    public function __destruct()
    {
        
    }
}
