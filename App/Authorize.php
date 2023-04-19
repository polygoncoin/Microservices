<?php
namespace App;

use App\HttpRequest;
use App\HttpErrorResponse;
use App\Connection;

/**
 * Class handles Authorization
 *
 * This class is built to process and handle Authorization
 *
 * @category   Cache
 * @package    Microservices
 * @author     Ramesh Narayan Jangid
 * @copyright  Ramesh Narayan Jangid
 * @version    Release: @1.0.0@
 * @since      Class available since Release 1.0.0
 */
class Authorize extends HttpRequest
{
    public $authirizationHeader = null;
    public $httpMethod = null;
    public $requestIP = null;

    /**
     * Server connection object
     *
     * @var object
     */
    public $conn = null;

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
     * Client DB
     *
     * @var string
     */
    public $clientDB = null;

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
     * Initialize authorization
     *
     * @return void
     */
    public static function init($authirizationHeader, $httpMethod, $requestIP)
    {
        $this->authirizationHeader = $authirizationHeader;
        $this->httpMethod = $httpMethod;
        $this->requestIP = $requestIP;

        $this->conn = new Connection();
        $this->process();
    }

    /**
     * Process authorization
     *
     * @return void
     */
    function process()
    {
        $this->setToken($_SERVER['HTTP_AUTHORIZATION']);
        if ($this->tokenExists($this->token)) {
            $this->parseRoute($_SERVER['REQUEST_METHOD'],$requestUri);
            $this->loadTokenSession($this->token);
            $this->checkSourceIp($_SERVER['REMOTE_ADDR']);
            $this->checkRoutePrivilage($this->groupId, $this->configuredUri);
        } else {
            HttpErrorResponse::return404('Token expired');
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
        return $this->conn->cacheExists($token);
    }

    /**
     * Load session with help of token
     *
     * @param string $token Bearer token
     * @return void
     */
    private function loadTokenSession($token)
    {
        $this->readOnlySession = json_decode($this->conn->getCache($token), true);

        if (empty($this->readOnlySession['id']) || empty($this->readOnlySession['group_id'])) {
            HttpErrorResponse::return404('Invalid session');
        } else {
            $this->userId = $this->readOnlySession['id'];
            $this->groupId = $this->readOnlySession['group_id'];
            $this->globalDB = $this->readOnlySession['global_db'];
            $this->clientDB = $this->readOnlySession['client_db'];
        }
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
        if ($this->conn->cacheExists("group:{$this->groupId}:ips")) {
            $foundIP = false;
            foreach(json_decode($this->conn->getCache("group:{$this->groupId}:ips"), true) as list($start, $end)) {
                if ($ipNumber >= $start && $ipNumber <= $end) {
                    $foundIP = true;
                    break;
                }
            }
        }
        if (!is_null($foundIP) && !$foundIP) {
            HttpErrorResponse::return404('IP Address not supported');
        }
    }

    /**
     * Check Requested route privilage
     *
     * @param int    $groupId Group ID
     * @param string $route   Raw route configured in Routes folder.
     * @return void
     */
    private function checkRoutePrivilage($groupId, $route)
    {
        $key = "group:{$groupId}:routes";
        if (!$this->conn->cacheSetValueExists($key, $route)) {
            HttpErrorResponse::return404('Route not supported');
        }
    }
    
    /**
     * Destructor
     */
    public function __destruct()
    {
        
    }
}
