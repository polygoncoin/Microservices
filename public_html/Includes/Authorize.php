<?php
/*
MIT License 

Copyright (c) 2023 Ramesh Narayan Jangid. 

Permission is hereby granted, free of charge, to any person obtaining a copy 
of this software and associated documentation files (the "Software"), to deal 
in the Software without restriction, including without limitation the rights 
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell 
copies of the Software, and to permit persons to whom the Software is 
furnished to do so, subject to the following conditions: 

The above copyright notice and this permission notice shall be included in all 
copies or substantial portions of the Software. 

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR 
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, 
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE 
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER 
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, 
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE 
SOFTWARE. 
*/
require_once __DOC_ROOT__ . '/public_html/Includes/HttpRequest.php';
require_once __DOC_ROOT__ . '/public_html/Includes/HttpErrorResponse.php';
require_once __DOC_ROOT__ . '/public_html/Includes/Connection.php';
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
        if ($this->conn->cacheExists('user_ips_' . $this->userId)) {
            $foundIP = false;
            foreach(json_decode($this->conn->getCache('user_ips_' . $this->userId), true) as list($start, $end)) {
                if ($ipNumber >= $start && $ipNumber <= $end) {
                    $foundIP = true;
                    break;
                }
            }
        }
        if ($this->conn->cacheExists('allowed_ips_' . $this->groupId)) {
            $foundIP = false;
            foreach(json_decode($this->conn->getCache('group_ips_' . $this->groupId), true) as list($start, $end)) {
                if ($ipNumber >= $start && $ipNumber <= $end) {
                    $foundIP = true;
                    break;
                }
            }
        }
        if (!is_null($foundIP) && !$foundIP) HttpErrorResponse::return404('IP Address not supported');
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
