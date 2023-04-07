<?php
//include method route file.
//define('__DOC_ROOT__', realpath(__DIR__ . '/../'));
//define('__REQUEST_URI__', trim($_GET['REQUEST_URI'], '/'));
/*
$JsonEncode = new JsonEncode();
$JsonEncode->startArray();
$JsonEncode->startArray();
$JsonEncode->endArray();
$JsonEncode->startArray();
$JsonEncode->endArray();
$JsonEncode->startArray();
$JsonEncode->endArray();
$JsonEncode->startArray();
$JsonEncode->endArray();
$JsonEncode->startArray();
$JsonEncode->endArray();
$JsonEncode->startArray();
$JsonEncode->endArray();
$JsonEncode->startArray();
$JsonEncode->endArray();
$JsonEncode->startArray();
$JsonEncode->endArray();
$JsonEncode->startArray();
$JsonEncode->endArray();
$JsonEncode->startArray();
$JsonEncode->endArray();
$JsonEncode->endArray();
die;
 */
header('Content-Type: application/json; charset=utf-8');
$JsonEncode = new JsonEncode();
$JsonEncode->startArray();
$JsonEncode->startAssoc();
$JsonEncode->endAssoc();
$JsonEncode->startAssoc();
$JsonEncode->endAssoc();
$JsonEncode->startArray();
$JsonEncode->startAssoc();
$JsonEncode->startArray('12');
$JsonEncode->endArray();
$JsonEncode->startArray('1');
$JsonEncode->endArray();
$JsonEncode->endAssoc();
$JsonEncode->startAssoc();
$JsonEncode->endAssoc();
$JsonEncode->endArray();
$JsonEncode->endArray();
$JsonEncode->end();
die;
/**
 * Converts array to JSON
 *
 * @category   JSON
 * @package    Microservices
 * @author     Ramesh Narayan Jangid
 * @copyright  Ramesh Narayan Jangid
 * @license    http://www.php.net/license/3_01.txt  PHP License 3.01
 * @version    Release: @1.0.0@
 * @since      Class available since Release 1.0.0
 */
class JsonEncode
{
    private $objects = [];
    private $currentObject = null;

    public function __construct()
    {

    }

    private function escape($str)
    {
        $escapers = array("\\", "/", "\"", "\n", "\r", "\t", "\x08", "\x0c");
        $replacements = array("\\\\", "\\/", "\\\"", "\\n", "\\r", "\\t", "\\f", "\\b");
        $str = str_replace($escapers, $replacements, $str);
        return '"' . $str . '"';
    }
    
    private function isAssociativeArray(&$arr)
    {
        $isAssociativeArray = false;
        $i = 0;
        foreach ($arr as $key => &$value) {
            if ($i != $key) {
                $isAssociativeArray = true;
                break;
            }
            $i++;
        }
        return $isAssociativeArray;
    }

    private function encode(&$arr)
    {
        if (!is_array($arr)) {
            echo $this->escape($arr);
            return;
        }
        $comma = '';
        $arrayComma = '';
        $assoc = self::isAssociativeArray($arr);
        echo ($assoc) ? '{' : '[';
        foreach ($arr as $key => $value) {
            $isNumeric = is_numeric($key);
            if (is_array($value)) {
                echo $arrayComma;
                $this->encode($value);
                $arrayComma = ',';
            } else {
                if (!$assoc) {
                    echo $comma . $this->escape($value);
                } else {
                    echo $comma . $this->escape($key) . ':' . $this->escape($value);
                }
                $comma = ',';
            }
            unset($arr[$key]);
        }
        echo ($assoc) ? '}' : ']';
    }

    public function addValue($value)
    {
        if ($this->currentObject->mode !== 'Array') {
            throw new Exception('Mode should be Array');
        }
        echo $this->currentObject->comma;
        $this->encode($value);
        $this->currentObject->comma = ',';
    }

    public function addKeyValue($key, $value)
    {
        if ($this->currentObject->mode !== 'Assoc') {
            throw new Exception('Mode should be Assoc');
        }
        echo $this->currentObject->comma;
        echo $this->escape($key) . ':';
        echo $this->encode($value);
        $this->currentObject->comma = ',';
    }

    public function startArray($key = null)
    {
        if ($this->currentObject) {
            echo $this->currentObject->comma;
            array_push($this->objects, $this->currentObject);
        }
        $this->currentObject = new JsonEncodeObject('Array');
        if (!is_null($key)) {
            echo $this->escape($key) . ':';
        }
        echo '[';
    }

    public function endArray()
    {
        echo ']';
        $this->currentObject = null;
        if (count($this->objects)>0) {
            $this->currentObject = array_pop($this->objects);
            $this->currentObject->comma = ',';
        }
    }

    public function startAssoc($key = null)
    {
        if ($this->currentObject) {
            echo $this->currentObject->comma;
            array_push($this->objects, $this->currentObject);
        }
        $this->currentObject = new JsonEncodeObject('Assoc');
        if (!is_null($key)) {
            echo $this->escape($key) . ':';
        }
        echo '{';
    }

    public function endAssoc()
    {
        echo '}';
        $this->currentObject = null;
        if (count($this->objects)>0) {
            $this->currentObject = array_pop($this->objects);
            $this->currentObject->comma = ',';
        }
    }

    public function end()
    {
        if ($this->currentObject || count($this->objects)>0) {
            die('Mismatch in JsonEncode function calls');
        }
    }

    public function __destruct()
    {
        $this->end();
    }
}
class JsonEncodeObject
{
    public $mode = '';
    public $comma = '';

    public function __construct($mode)
    {
        $this->mode = $mode;
    }
}

class HttpResponse
{
    static function return404($errMessage, $customise = false)
    {
        if ($customise) {
            $arr = explode('|', $errMessage);
            self::returnHttpStatus(
                404,
                [
                    'Error' => [
                        'Parameter' => $arr[0],
                        'Supported Values' => explode(',', $arr[1])
                    ]
                ]
            );
        } else {
            self::returnHttpStatus(
                404,
                ['message' => $errMessage]
            );
        }
    }
    static function return501($errMessage)
    {
        self::returnHttpStatus(
            501,
            ['message' => $errMessage]
        );
    }

    private function returnHttpStatus($statusCode, &$arr)
    {
        $this->returnJSON(
            array_merge(
                ['status' => $statusCode],
                $arr
            )
        );
    }

    private function JSON(&$arr)
    {
        JSON::encode($arr);
    }
}
class HttpRequest
{
    public $routeParams = [];
    public $uriElements = [];
    public $routesConfig = null;
    public $route = null;

    public $requestMethod = null;

    public function __construct()
    {
        $this->requestMethod = $_SERVER['REQUEST_METHOD'];

    }

    function loadRoute($requestUri)
    {
        $routeFileLocation = __DOC_ROOT__ . '/app/routes/' . $this->requestMethod . 'routes.php';
        if (file_exists($routeFileLocation)) {
            $routes = require $routeFileLocation;
        } else {
            HttpRequest::return501('Missing' . ' route file for' . " $this->requestMethod " . 'method');
        }
        foreach(explode('/', $requestUri) as $key => $element) {
            $pos = false;
            if (isset($routes[$element])) {
                $this->uriElements[] = $element;
                $routes = &$routes[$element];
            } else {
                if (is_array($routes)) {
                    foreach (array_keys($routes) as $key) {
                        $pos = strpos($key, '{');
                        if ($pos === 0) {
                            $keyArr = explode('|', $key);
                            $keyDetails = explode(':', trim($keyArr[0], '{}'));
                            $keysDetail[$keyDetails[1]] = [$key, $keyDetails[0]];
                            $found = true;
                        }
                    }
                    if ($found) {
                        switch (true) {
                            case isset($keysDetail['int']) && ctype_digit($element):
                                $restrictedArr = explode('|', $keysDetail['int'][0]);
                                if (!empty($restrictedArr[1]) && !in_array($element, explode(',', $restrictedArr[1]))) {
                                    HttpRequest::return404($keysDetail['int'][0], true);
                                }
                                $routeParams[$keysDetail['int'][1]] = (int)$element;
                                $this->uriElements[] = $keysDetail['int'][0];
                                $routes = &$routes[$keysDetail['int'][0]];
                                break;
                            case isset($keysDetail['string']):
                                $restrictedArr = explode('|', $keysDetail['string'][0]);
                                if (!empty($restrictedArr[1]) && !in_array($element, explode(',', $restrictedArr[1]))) {
                                    HttpRequest::return404($keysDetail['string'][0], true);
                                }
                                $this->routeParams[$keysDetail['string'][1]] = $element;
                                $this->uriElements[] = $keysDetail['string'][0];
                                $routes = &$routes[$keysDetail['string'][0]];
                                break;
                        }
                    } else {
                        HttpRequest::return404('Route not supported');
                    }
                } else {
                    HttpRequest::return404('Route not supported');
                }
            }
        }
        $this->route = '/' . implode('/',$this->uriElements);
    }
}

class Cache
{
    private $masterHostname = null;
    private $masterPassword = null;
    private $masterPort = null;

    private $slaveHostname = null;
    private $slavePassword = null;
    private $slavePort = null;

    public $master = null;
    public $slave = null;
    private $useSlave = null;

    function __construct()
    {
        $this->masterHostname = $_SESSION['CacheMasterHostname'];
        $this->masterPassword = $_SESSION['CacheMasterPassword'];
        $this->masterPort = $_SESSION['CacheMasterPort'];

        $this->useSlave = $_SESSION['useSlave'];

        switch ($this->useSlave) {
            case true:
                $this->slaveHostname = $_SESSION['CacheSlaveHostname'];
                $this->slavePassword = $_SESSION['CacheSlavePassword'];
                $this->slavePort = $_SESSION['CacheSlavePort'];
                break;                    
            case false:
                $this->slaveHostname = $_SESSION['CacheMasterHostname'];
                $this->slavePassword = $_SESSION['CacheMasterPassword'];
                $this->slavePort = $_SESSION['CacheMasterPort'];
                break;                    
        }
    }

    function connectRedisServer($mode)
    {
        $hostname = $mode.'Hostname';
        $password = $mode.'Password';
        $port = $mode.'Port';

        try {
            $redis = new Redis();
            $redis->connect($this->$hostname, $this->$port, 1, NULL, 100);
            $redis->ping();
        } catch (\Exception $e) {
            HttpRequest::return501('Unable to connect to token cache server');
        }
        return $redis;
    }

    /**
     * Undocumented function
     *
     * @param [type] $mode master/slave
     * @return void
     */
    function loadRedisServer($mode)
    {
        $this->$mode = $this->connectRedisServer($mode);
    }
}

class Database
{
    private $masterHostname = null;
    private $masterUsername = null;
    private $masterPassword = null;
    private $masterDatabase = null;

    private $slaveHostname = null;
    private $slaveUsername = null;
    private $slavePassword = null;
    private $slaveDatabase = null;

    public $master = null;
    public $slave = null;
    private $useSlave = null;

    function __construct()
    {
        $this->masterHostname = $_SESSION['DbMasterHostname'];
        $this->masterUsername = $_SESSION['DbMasterUsername'];
        $this->masterPassword = $_SESSION['DbMasterPassword'];
        $this->masterDatabase = $_SESSION['DbMasterDatabase'];

        $this->useSlave = $_SESSION['useSlave'];

        switch ($this->useSlave) {
            case true:
                $this->slaveHostname = $_SESSION['DbSlaveHostname'];
                $this->slaveUsername = $_SESSION['DbSlaveUsername'];
                $this->slavePassword = $_SESSION['DbSlavePassword'];
                $this->slaveDatabase = $_SESSION['DbSlaveDatabase'];
                break;                    
            case false:
                $this->slaveHostname = $_SESSION['DbMasterHostname'];
                $this->slaveUsername = $_SESSION['DbMasterUsername'];
                $this->slavePassword = $_SESSION['DbMasterPassword'];
                $this->slaveDatabase = $_SESSION['DbMasterDatabase'];
                break;                    
        }
    }

    function connectMySqlServer($mode)
    {
        $hostname = $mode.'Hostname';
        $username = $mode.'Username';
        $password = $mode.'Password';
        $database = $mode.'Database';

        try {
            $connection = new \PDO(
                "mysql:host={$this->$hostname};dbname={$this->$database}",
                $this->$username,
                $this->$password,
                [
                    \PDO::ATTR_EMULATE_PREPARES => false
                ]
            );
        } catch (\PDOException $e) {
            HttpRequest::return501('Unable to connect to database server');
        }

        return $connection;
    }

    /**
     * Undocumented function
     *
     * @param [type] $mode master/slave
     * @return void
     */
    function loadMySqlServer($mode)
    {
        $this->$mode = $this->connectMySqlServer($mode);
    }
}

class Connection
{
    public $cache = null;
    public $db = null;

    function __construct()
    {
        $this->cache = new Cache();
        $this->db = new Database();
    }
}

class Authorize
{
    public $token = null;
    public $conn = null;

    public $userId = null;
    public $groupId = null;

    public static function init()
    {
        self::process();
    }

    function process()
    {
        $this->conn = new Connection();
        $this->processToken($_SERVER['HTTP_AUTHORIZATION']);
        $this->loadSession();
        $this->checkSourceIp($_SERVER['REMOTE_ADDR']);        
    }

    function processToken($authHeader)
    {
        if (preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
            $this->token = $matches[1];
        } else {
            HttpResponse::return404('Missing token in authorization header');    
        }
        if (empty($this->token)) {
            HttpResponse::return404('Missing token');
        }
    }

    function loadSession()
    {
        if ($this->conn->cache->slave->exists($this->token)) {
            $_SESSION = json_decode($this->conn->cache->slave->get($this->token), true);
        } else {
            HttpResponse::return404('Token expired');
        }

        if (empty($_SESSION['id']) || empty($_SESSION['group_id'])) {
            HttpResponse::return404('Invalid token');
        } else {
            $this->userId = $_SESSION['id'];
            $this->groupId = $_SESSION['group_id'];
        }
    }

    function checkSourceIp($ip)
    {
        $ipNumber = ip2long($ip);
        if ($this->conn->cache->slave->exists('user_ips_' . $this->userId)) {
            $foundIP = false;
            foreach(json_decode($this->conn->cache->slave->get('user_ips_' . $this->userId), true) as list($start, $end)) {
                if ($ipNumber >= $start && $ipNumber <= $end) {
                    $foundIP = true;
                    break;
                }
            }
            if (!$foundIP) return404('IP Address not supported');
        }
        if ($this->conn->cache->slave->exists('allowed_ips_' . $this->groupId)) {
            $foundIP = false;
            foreach(json_decode($this->conn->cache->slave->get('group_ips_' . $this->groupId), true) as list($start, $end)) {
                if ($ipNumber >= $start && $ipNumber <= $end) {
                    $foundIP = true;
                    break;
                }
            }
            if (!$foundIP) return404('IP Address not supported');
        }
    }

    function __destruct()
    {
        
    }
}


if ($redis->exists('crud_' . $groupID)) {
    $routeDetails = json_decode($redis->get($groupID . '_' . $routeBase64), true);
    if (!isset($routeDetails[$method])) {
        HttpRequest::return404('Method not supported');
    } else {
        $clientID = $routeDetails[$method]['client_id'];
        $moduleID = $routeDetails[$method]['module_id'];
    }
} else {
    HttpRequest::return404('Route details not present on cache server');
}

//include route configuration file.
if (empty($routes['__file__'])) {
    HttpRequest::return501('Missing' . ' crud file configuration for' . " $method " . 'method');
}
if (file_exists($routes['__file__'])) {
    include $routes['__file__'];
} else {
    HttpRequest::return501('Missing' . ' crud file for' . " $method " . 'method');
}

//validate payload params
if (in_array($method, ['POST', 'PUT'])) {
    $payloadKeys = array_keys($payload['required']);
    if (isset($config['payload'])) {
        foreach (array_merge($config['payload']['required'], $config['payload']['optional']) as $value) {
            if (!in_array($value, $payloadKeys)) {
                HttpRequest::return404("Missing required param '$value' in payload");
            } elseif (isset($payload[$value])) {
                $params[':'.$value] = $payload[$value];
            }
        }
    }
}

class Init
{
    private $result = [];

    public static function api()
    {
        Authorize::init();

        switch ($method) {
            case 'GET':
                JSON::encode(self::httpGET(), $assoc);
                break;
            case 'POST':
                JSON::encode(self::httpPOST(), $assoc);
                $this->httpPOST();
                break;
            case 'PUT':
                JSON::encode(self::httpPUT(), $assoc);
                break;
            case 'DELETE':
                JSON::encode(self::httpDELETE(), $assoc);
                break;
        }
    }
    function httpGET()
    {
        if (isset($queries['default'])) {
            $sth = $connection->prepare($queries['default'][0], array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
            $sth->execute($queries['default'][1]);
            if ($queries['default'][2] === 'singleRowFormat') {
                $result = $sth->fetch(PDO::FETCH_ASSOC);
                $sth->closeCursor();
            }
            if ($queries['default'][2] === 'multipleRowFormat') {
                $result = $sth->fetchAll(PDO::FETCH_ASSOC);
                $sth->closeCursor();
                return;
            }
        }
        if (isset($queries['default'][2]) && $queries['default'][2] === 'singleRowFormat') {
            foreach ($queries as $key => &$value) {
                if ($key === 'default') continue;
                $sth = $connection->prepare($value[0], array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
                $sth->execute($value[1]);
                $result[$key] = $sth->fetchAll(PDO::FETCH_ASSOC);
                if ($queries[$key][2] === 'singleRowFormat') {
                    $result[$key] = $sth->fetch(PDO::FETCH_ASSOC);
                }
                if ($queries[$key][2] === 'multipleRowFormat') {
                    $result[$key] = $sth->fetchAll(PDO::FETCH_ASSOC);
                }
                $sth->closeCursor();
            }
        }
    }
    function httpPOST()
    {
        foreach ($queries as $key => &$value) {
            $sth = $connection->prepare($value[0], [PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY]);
            $sth->execute($value[1]);
            $result[$key] = $connection->lastInsertId();
            $sth->closeCursor();
        }
    }
    function httpPUT()
    {
        foreach ($queries as $key => &$value) {
            $sth = $connection->prepare($value[0], [PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY]);
            $sth->execute($value[1]);
            $result[$key] = $sth->rowCount();
            $sth->closeCursor();
        }
    }
    function httpDELETE()
    {
        foreach ($queries as $key => &$value) {
            $sth = $connection->prepare($value[0], [PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY]);
            $sth->execute($value[1]);
            $result[$key] = $sth->rowCount();
            $sth->closeCursor();
        }
    }
}

Init::api();