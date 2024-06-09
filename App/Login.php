<?php
namespace App;

use App\Constants;
use App\Env;
use App\HttpResponse;
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
     * Payload
     *
     * @var array
     */
    private $payload = [];

    /**
     * Login Initialization
     *
     * @return void
     */
    public function init()
    {
        Env::$cacheType = getenv('cacheType');
        Env::$cacheHostname = getenv('cacheHostname');
        Env::$cachePort = getenv('cachePort');
        Env::$cacheUsername = getenv('cacheUsername');
        Env::$cachePassword = getenv('cachePassword');
        Env::$cacheDatabase = getenv('cacheDatabase');

        $this->cache = Cache::getObject();
        $this->jsonObj = HttpResponse::getJsonObject();

        return true;
    }

    /**
     * Process authorization
     *
     * @return void
     */
    public function process()
    {
        $this->performBasicCheck();
        $this->loadUser();
        $this->validateRequestIp();
        $this->validatePassword();
        $this->outputTokenDetails();

        return true;
    }

    /**
     * Function to perform basic checks
     *
     * @return void
     */
    private function performBasicCheck()
    {
        // Check request method is POST.
        if (Constants::$REQUEST_METHOD !== Constants::$POST) {
            HttpResponse::return4xx(404, 'Invalid request method');
            return;
        }
        if (isset($_POST['Payload'])) {
            $this->payload = json_decode($_POST['Payload'], true);
        }
        // Check for required input variables
        foreach (array('username','password') as $value) {
            if (!isset($this->payload[$value]) || empty($this->payload[$value])) {
                HttpResponse::return4xx(404, 'Missing required parameters');
                return;
            } else {
                $this->$value = $this->payload[$value];
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
        if ($this->cache->cacheExists("user:{$this->payload['username']}")) {
            $this->userDetails = json_decode($this->cache->getCache("user:{$this->payload['username']}"), true);
            $this->userId = $this->userDetails['user_id'];
            $this->groupId = $this->userDetails['group_id'];
            if (empty($this->userId) || empty($this->groupId)) {
                HttpResponse::return4xx(404, 'Invalid credentials');
                return;
            }            
        } else {
            HttpResponse::return4xx(404, 'Invalid credentials.');
            return;
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
                if (cidr_match(Constants::$REMOTE_ADDR, $cidr)) {
                    $isValidIp = true;
                    break;
                }
            }
            if (!$isValidIp) {
                HttpResponse::return4xx(404, 'Invalid credentials.');
                return;
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
            return;
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
                $this->cache->setCache($token, '{}', Constants::$TOKEN_EXPIRY_TIME);
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
                if ((Constants::$TOKEN_EXPIRY_TIME - ($this->timestamp - $tokenDetails['timestamp'])) > 0) {
                    $tokenFound = true;
                } else {
                    $this->cache->deleteCache($tokenDetails['token']);
                }
            }
        }
        if (!$tokenFound) {
            $tokenDetails = $this->generateToken();
            // We set this to have a check first if multiple request/attack occurs.
            $this->cache->setCache("usertoken:{$this->userId}", json_encode($tokenDetails), Constants::$TOKEN_EXPIRY_TIME);
            $this->cache->setCache($tokenDetails['token'], json_encode($this->userDetails), Constants::$TOKEN_EXPIRY_TIME);
            $this->updateDB($tokenDetails);
        }
        $output = [
            'Token' => $tokenDetails['token'],
            'Expires' => (Constants::$TOKEN_EXPIRY_TIME - ($this->timestamp - $tokenDetails['timestamp']))
        ];
        $this->jsonObj->addKeyValue('Results', $output);
    }

    private function updateDB($tokenDetails)
    {
        Env::$dbType = getenv('defaultDbType');
        Env::$dbHostname = getenv('defaultDbHostname');
        Env::$dbPort = getenv('defaultDbPort');
        Env::$dbUsername = getenv('defaultDbUsername');
        Env::$dbPassword = getenv('defaultDbPassword');
        Env::$dbDatabase = getenv('defaultDbDatabase');

        $this->db = Database::getObject();
        $userTable = Env::$users;
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
