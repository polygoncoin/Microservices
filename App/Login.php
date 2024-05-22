<?php
namespace App;

use App\Constants;
use App\HttpRequest;
use App\HttpResponse;
use App\Logs;
use App\Servers\Cache\Cache;
use App\Servers\Database\Database;

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
     * DB Server connection object
     *
     * @var object
     */
    public $db = null;

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
     * JsonEncode class object
     *
     * @var object
     */
    public $jsonObj = null;

    /**
     * Login Initialization
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
        Cache::$cacheType = 'cacheType';
        Cache::$hostname = 'cacheHostname';
        Cache::$port = 'cachePort';
        Cache::$username = 'cacheUsername';
        Cache::$password = 'cachePassword';
        Cache::$database = 'cacheDatabase';

        $this->cache = Cache::getObject();
        $this->jsonObj = HttpResponse::getJsonObject();
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
        $this->requestIp = REMOTE_ADDR;

        // Check request method is POST.
        if (REQUEST_METHOD !== Constants::CREATE) {
            HttpResponse::return4xx(404, 'Invalid request method');
        }

        // Check for required input variables
        foreach (array('username','password') as $value) {
            if (!isset($_POST[$value]) || empty($_POST[$value])) {
                HttpResponse::return4xx(404, 'Missing required parameters');
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
            $this->userId = $this->userDetails['user_id'];
            $this->groupId = $this->userDetails['group_id'];
            if (empty($this->userId) || empty($this->groupId)) {
                HttpResponse::return4xx(404, 'Invalid credentials');
            }            
        } else {
            HttpResponse::return4xx(404, 'Invalid credentials.');
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
        if ($this->cache->cacheExists("cidr:{$this->groupId}")) {
            $cidrs = json_decode($this->cache->getCache("cidr:{$this->groupId}"), true);
            $isValidIp = false;
            foreach ($cidrs as $cidr) {
                if (cidr_match($this->requestIp, $cidr)) {
                    $isValidIp = true;
                    break;
                }
            }
            if (!$isValidIp) {
                HttpResponse::return4xx(404, 'Invalid credentials.');
            }
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
            HttpResponse::return4xx(404, 'Invalid credentials');
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
                $this->cache->setCache($token, '{}', TOKEN_EXPIRY_TIME);
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
        if ($this->cache->cacheExists("usertoken:{$this->userId}")) {
            $tokenDetails = json_decode($this->cache->getCache("usertoken:{$this->userId}"), true);
            if ($this->cache->cacheExists($tokenDetails['token'])) {
                if ((TOKEN_EXPIRY_TIME - ($this->timestamp - $tokenDetails['timestamp'])) > 0) {
                    $tokenFound = true;
                } else {
                    $this->cache->deleteCache($tokenDetails['token']);
                }
            }
        }
        if (!$tokenFound) {
            $tokenDetails = $this->generateToken();
            // We set this to have a check first if multiple request/attack occurs.
            $this->cache->setCache("usertoken:{$this->userId}", json_encode($tokenDetails), TOKEN_EXPIRY_TIME);
            $this->cache->setCache($tokenDetails['token'], json_encode($this->userDetails), TOKEN_EXPIRY_TIME);
            $this->updateDB($tokenDetails);
        }
        $output = [
            'Token' => $tokenDetails['token'],
            'Expires' => (TOKEN_EXPIRY_TIME - ($this->timestamp - $tokenDetails['timestamp']))
        ];
        $this->jsonObj->addKeyValue('Results', $output);
    }

    private function updateDB($tokenDetails)
    {
        Database::$dbType = 'defaultDbType';
        Database::$hostname = 'defaultDbHostname';
        Database::$port = 'defaultDbPort';
        Database::$username = 'defaultDbUsername';
        Database::$password = 'defaultDbPassword';
        Database::$database = 'defaultDbDatabase';

        $this->db = Database::getObject();
        $userTable = getenv('users');
        $this->db->execDbQuery("
        UPDATE
            `{$userTable}`
        SET
            `token` = :token,
            `token_ts` = :token_ts
        WHERE
            user_id = :user_id",
        [
            ':token' => $tokenDetails['token'],
            ':token_ts' => $tokenDetails['timestamp'],
            ':user_id' => $this->userId
        ]);
    }
}
