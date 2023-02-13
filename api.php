<?php
header('Content-Type: application/json; charset=utf-8');
$token = null;
if (isset($_SERVER['HTTP_AUTHORIZATION'])) { //Nginx or fast CGI
    $headers = trim($_SERVER["HTTP_AUTHORIZATION"]);
    if (preg_match('/Bearer\s(\S+)/', $headers, $matches)) {
        $token = $matches[1];
    }
}
if (!$token) {
    echo '{"status":401,"message":"Missing token"}';die;
}
if (!isset($_GET['crud']) {
    echo '{"status":404,"message":"Missing required params"}';die;
}

define('REDIS_HOST','127.0.0.1');
define('REDIS_PORT','6379');

$redis = new Redis();
$redis->connect(REDIS_HOST, REDIS_PORT, 1, NULL, 100);

try {
    $redis->ping();
} catch (Exception $e) {
    echo '{"status":501,"message":"REDIS NOT CONNECTED '.$e->getMessage().'"}';
}
//check token in redis.
if ($sess = $redis->get($token)) {
    $_SESSION = json_decode($sess, true);
    unset($sess);
} else {
    echo '{"status":401,"message":"Token expired"}';die;
}
//check session
if (!isset($_SESSION['user']['id'])) {
    echo '{"status":401,"message":"Invalid token"}';die;
}
//collect params in $_POST
$method = $_SERVER['REQUEST_METHOD'];
if (in_array($method, ['POST', 'PUT'])) {
    parse_str(file_get_contents('php://input'), $payload);
}
// validate crud params
if (!in_array($method, $_SESSION['cruds'][$_POST['crud']])) {
    echo '{"status":401,"message":"Method not supported"}';die;
}
//include method route file.
include 'routes/' . $method . 'routes.php';
// parse/validate route
$value = [];
foreach(explode('/', $_SERVER['REQUEST_URI']) as $element) {
    $pos = false;
    if (isset($routes[$element])) {
        $routes = &$routes[$element];
    } else {
        foreach (array_keys($routes) as $key) {
            $pos = strpos($key, '{');
            if ($pos === 0) {
                $uriParams[':' . trim($key, '{}')] = $element;
                break;
            }
        }
        if ($pos === 0) {
            continue;
        } else {
            echo '{"status":404,"message":"Route not supported"}';die;
        }
    }
}
//include route configuration file.
if (!file_exists(__DIR__ . '/crud/' . $method . '/'. $routes['file'])) {
    echo '{"status":501,"message":"Crud file missing"}';die;
}
include __DIR__ . '/crud/' . $method . '/'. $routes['file'];
//validate payload params
$params = $config['uriParams'];
if (in_array($method, ['POST', 'PUT'])) {
    $payloadKeys = array_keys($payload['required']);
    if (isset($config['payload'])) {
        foreach (array_merge($config['payload']['required'], $config['payload']['optional']) as $value) {
            if (!in_array($value, $payloadKeys)) {
                echo '{"status":404,"message":"Missing required param \''.$value.'\' in payload"}';die;
            } elseif (isset($payload[$value])) {
                $params[':'.$value] = $payload[$value];
            }
        }
    }
}
//perform DB operation based on crud config.
try {
    $dbhost = 'localhost';
    $dbname = 'hr';
    $dbuser = 'root';
    $dbpass = '';
    $connec = new PDO("mysql:host=$dbhost;dbname=$dbname", $dbuser, $dbpass);
}catch (PDOException $e) {
    echo "Error : " . $e->getMessage() . "<br/>";
    die();
}
/* Execute a prepared statement by passing an array of values */
$result = [];
switch ($method) {
    case 'GET':
        foreach ($queries as $key => &$value) {
            $sth = $connec->prepare($value[0], array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
            $sth->execute($value[1]);
            $result[$key] = $sth->fetchAll();
            $sth->closeCursor();
        }
        break;
    case 'POST':
        foreach ($queries as $key => &$value) {
            $sth = $connec->prepare($value[0], [PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY]);
            $sth->execute($value[1]);
            $result[$key] = $connec->lastInsertId();
            $sth->closeCursor();
        }
        break;
    case 'PUT':
        foreach ($queries as $key => &$value) {
            $sth = $connec->prepare($value[0], [PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY]);
            $sth->execute($value[1]);
            $result[$key] = $sth->rowCount();
            $sth->closeCursor();
        }
        break;
    case 'DELETE':
        foreach ($queries as $key => &$value) {
            $sth = $connec->prepare($value[0], [PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY]);
            $sth->execute($value[1]);
            $result[$key] = $sth->rowCount();
            $sth->closeCursor();
        }
        break;
}
// output JSON.
echo json_encode($result);die;
