<?php
namespace App;

use App\HttpRequest;
use App\HttpErrorResponse;
use App\JsonEncode;
use App\Servers\Cache;

/**
 * Login
 *
 * This class is used for login and generates token for user
 *
 * @category   Login
 * @package    Microservices
 * @author     Ramesh Narayan Jangid
 * @copyright  Ramesh Narayan Jangid
 * @version    Release: @1.0.0@
 * @since      Class available since Release 1.0.0
 */
class Login
{
    /**
     * Cache Server cacheection object
     *
     * @var object
     */
    private $cache = null;

    /**
     * Username for login
     *
     * @var string
     */
    private $username = null;

    /**
     * Password for login
     *
     * @var string
     */
    private $password = null;

    /**
     * IP from which request originated
     *
     * @var string
     */
    private $requestIp = null;

    /**
     * Details pertaining to user.
     *
     * @var array
     */
    private $userDetails;
    
    /**
     * User ID
     *
     * @var int
     */
    private $userId;

    /**
     * Group ID
     *
     * @var int
     */
    private $groupId;

    /**
     * Current timestamp
     *
     * @var int
     */
    private $timestamp;
    
    /**
     * Login Initialization
     *
     * @return void
     */
    public static function init()
    {
        (new self)->process();
    }

    /**
     * Process authorization
     *
     * @return void
     */
    private function process()
    {
        $this->cache = new Cache(
            'cacheHostname',
            'cachePort',
            'cachePassword',
            'cacheDatabase'
        );
        $this->performBasicCheck();
        $this->loadUser();
        $this->validateRequestIp();
        $this->validatePassword();
        $this->outputTokenDetails();
    }

    /**
     * Function to perform basic checks
     *
     * @return void
     */
    private function performBasicCheck()
    {
        // Check request not from proxy.
        if (!isset($_SERVER['REMOTE_ADDR'])) {
            http_response_code(404);
        }
        $this->requestIp = $_SERVER['REMOTE_ADDR'];

        // Check request method is POST.
        if ('POST' !== $_SERVER['REQUEST_METHOD']) {
            HttpErrorResponse::return4xx(404, 'Invalid request method');
        }

        // Check for required input variables
        foreach (array('username','password') as $value) {
            if (!isset($_POST[$value]) || empty($_POST[$value])) {
                HttpErrorResponse::return4xx(404, 'Missing required parameters');
            } else {
                $this->$value = $_POST[$value];
            }
        }
    }

    /**
     * Function to load user details from cache
     *
     * @return void
     */
    private function loadUser()
    {
        // Redis - one can find the userID from username.
        if ($this->cache->cacheExists("user:{$_POST['username']}")) {
            $this->userDetails = json_decode($this->cache->getCache("user:{$_POST['username']}"), true);
            $this->userId = $this->userDetails['id'];
            $this->groupId = $this->userDetails['group_id'];
            if (empty($this->userId) || empty($this->groupId)) {
                HttpErrorResponse::return4xx(404, 'Invalid credentials');
            }            
        } else {
            HttpErrorResponse::return4xx(404, 'Invalid credentials.');
        }
    }

    /**
     * Function to validate source ip.
     *
     * @return void
     */
    private function validateRequestIp()
    {
        // Redis - one can find the userID from username.
        if (
            $this->cache->cacheExists("group:{$this->groupId}:ips")
            && !$this->cache->isSetMember("group:{$this->groupId}:ips", $this->requestIp)
        )
        {
            HttpErrorResponse::return4xx(404, 'Invalid credentials.');
        }
    }

    /**
     * Validates password from its hash present in cache
     *
     * @return void
     */
    private function validatePassword()
    {
        if (!password_verify($this->password, $this->userDetails['password_hash'])) { // get hash from redis and compares with password
            HttpErrorResponse::return4xx(404, 'Invalid credentials');
        }
    }

    /**
     * Generates token
     *
     * @return array
     */
    private function generateToken()
    {
        //generates a crypto-secure 64 characters long
        while (true) {
            $token = bin2hex(random_bytes(32));
            if (!$this->cache->cacheExists($token)) {
                $this->cache->setCache($token, '{}', EXPIRY_TIME);
                $tokenDetails = ['token' => $token, 'timestamp' => $this->timestamp];
                break;
            }
        }
        return $tokenDetails;
    }

    /**
     * Outputs active/newly generated token details.
     *
     * @return void
     */
    private function outputTokenDetails()
    {
        $this->timestamp = time();
        $tokenFound = false;
        if ($this->cache->cacheExists("user:{$this->userId}:token")) {
            $tokenDetails = json_decode($this->cache->getCache("user:{$this->userId}:token"), true);
            if ($this->cache->cacheExists($tokenDetails['token'])) {
                $tokenFound = true;
            }
        }
        if (!$tokenFound) {
            $tokenDetails = $this->generateToken();
            // We set this to have a check first if multiple request/attack occurs.
            $this->cache->setCache("user:{$this->userId}:token", json_encode($tokenDetails), EXPIRY_TIME);
            $this->cache->setCache($tokenDetails['token'], json_encode($this->userDetails), EXPIRY_TIME);
        }
        $jsonEncode = new JsonEncode();
        $jsonEncode->encode(
            [
                'token' => $tokenDetails['token'],
                'expires' => (EXPIRY_TIME - ($this->timestamp - $tokenDetails['timestamp']))
            ]
        );
        $jsonEncode = null;
    }
}
