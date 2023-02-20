<?php
function return404($errMessage, $customise = false)
{
    if ($customise) {
        $arr = explode('|', $errMessage);
        echo json_encode([
            'status' => 404,
            'Error' => [
                'Parameter' => $arr[0],
                'Supported Values' => explode(',', $arr[1])
            ]
        ]);
    } else {
        echo '{"status":404,"message":"'.$errMessage.'."}';
    }
    die;
}
function return501($errMessage)
{
    echo '{"status":501,"message":"'.$errMessage.'."}';die;
}
header('Content-Type: application/json; charset=utf-8');
// Get token - START
$token = null;
if (isset($_SERVER['HTTP_AUTHORIZATION'])) { //Nginx or fast CGI
    $headers = trim($_SERVER["HTTP_AUTHORIZATION"]);
    if (preg_match('/Bearer\s(\S+)/', $headers, $matches)) {
        $token = $matches[1];
    } else {
        return404('Missing token in authorization header');    
    }
} else {
    return404('Missing authorization header');
}
if (empty($token)) {
    return404('Missing token');
}
// Get token - END

// Check token - START
// connection to Redis server for tokens.
define('TOKEN_REDIS_HOST','127.0.0.1');
define('TOKEN_REDIS_PORT','6379');
$redis = new Redis();
$redis->connect(TOKEN_REDIS_HOST, TOKEN_REDIS_PORT, 1, NULL, 100);
try {
    $redis->ping();
} catch (Exception $e) {
    return501('Unable to connect to token cache server');
}

//check token in redis.
if ($redis->exists($token)) {
    $_SESSION = json_decode($redis->get($token), true);
} else {
    return404('Token expired');
}
$redis = null;
// Check token - END
//check session
if (!isset($_SESSION['user']['id'])) {
    return404('Invalid token');
}
//collect params in $_POST
$method = $_SERVER['REQUEST_METHOD'];
//include method route file.
define('__DOC_ROOT__',__DIR__);
$REQUEST_URI = trim($_GET['REQUEST_URI'], '/');
if ((strpos($REQUEST_URI, 'crud/') !== false)) {// is a crud operation.
    $routeFileLocation = __DOC_ROOT__ . '/crudApi/routes/' . $method . 'routes.php';
} else {
    $routeFileLocation = __DOC_ROOT__ . '/customApi/routes/' . $method . 'routes.php';
}
if (file_exists($routeFileLocation)) {
    include $routeFileLocation;
} else {
    return501('Missing' . ' routes file for' . " $method " . 'method');
}
// Validate route
$uriParameters = [];
$crud = null;
foreach(explode('/', $REQUEST_URI) as $element) {
    if ($element === 'crud' && is_null($crud)) {
        $crud = true;
        continue;
    } else {
        $crud = false;
    }
    $pos = false;
    if (isset($routes[$element])) {
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
                            return404($keysDetail['int'][0], true);
                        }
                        $uriParameters[$keysDetail['int'][1]] = (int)$element;
                        $routes = &$routes[$keysDetail['int'][0]];
                        break;
                    case isset($keysDetail['string']):
                        $restrictedArr = explode('|', $keysDetail['string'][0]);
                        if (!empty($restrictedArr[1]) && !in_array($element, explode(',', $restrictedArr[1]))) {
                            return404($keysDetail['string'][0], true);
                        }
                        $uriParameters[$keysDetail['string'][1]] = $element;
                        $routes = &$routes[$keysDetail['string'][0]];
                        break;
                }
                
            } else {
                return404('Route not supported');
            }
        } else {
            return404('Route not supported');
        }
    }
}
// validate crud params
if (!isset($_SESSION['cruds'][$uriParameters['table']]) || !in_array($method, $_SESSION['cruds'][$uriParameters['table']])) {
    return404('Method not supported');
}
//include route configuration file.
if (empty($routes['__file__'])) {
    return501('Missing' . ' crud file configuration for' . " $method " . 'method');
}
if (file_exists($routes['__file__'])) {
    include $routes['__file__'];
} else {
    return501('Missing' . ' crud file for' . " $method " . 'method');
}
//validate payload params
if (in_array($method, ['POST', 'PUT'])) {
    $payloadKeys = array_keys($payload['required']);
    if (isset($config['payload'])) {
        foreach (array_merge($config['payload']['required'], $config['payload']['optional']) as $value) {
            if (!in_array($value, $payloadKeys)) {
                return404("Missing required param '$value' in payload");
            } elseif (isset($payload[$value])) {
                $params[':'.$value] = $payload[$value];
            }
        }
    }
}
//perform DB operation based on crud config.
try {
    define('MYSQL_HOSTNAME', '127.0.0.1');
    define('MYSQL_USERNAME', 'root');
    define('MYSQL_PASSWORD', '');
    define('MYSQL_DATABASE', 'product_global');
    $connection = new PDO('mysql:host='.MYSQL_HOSTNAME.';dbname='.MYSQL_DATABASE, MYSQL_USERNAME, MYSQL_PASSWORD, [PDO::ATTR_EMULATE_PREPARES => false]);
} catch (PDOException $e) {
    return501('Unable to connect to database server');
}
//print_r($queries);die;
/* Execute a prepared statement by passing an array of values */
$result = [];
switch ($method) {
    case 'GET':
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
                break;
            }
        }
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
        break;
    case 'POST':
        foreach ($queries as $key => &$value) {
            $sth = $connection->prepare($value[0], [PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY]);
            $sth->execute($value[1]);
            $result[$key] = $connection->lastInsertId();
            $sth->closeCursor();
        }
        break;
    case 'PUT':
        foreach ($queries as $key => &$value) {
            $sth = $connection->prepare($value[0], [PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY]);
            $sth->execute($value[1]);
            $result[$key] = $sth->rowCount();
            $sth->closeCursor();
        }
        break;
    case 'DELETE':
        foreach ($queries as $key => &$value) {
            $sth = $connection->prepare($value[0], [PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY]);
            $sth->execute($value[1]);
            $result[$key] = $sth->rowCount();
            $sth->closeCursor();
        }
        break;
}
$connection = null;
// output JSON.
echo json_encode($result);die;
