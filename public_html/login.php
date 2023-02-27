<?php
function return404($errMessage)
{
    echo '{"status":404,"message":"'.$errMessage.'."}';die;
}
function return501($errMessage)
{
    echo '{"status":501,"message":"'.$errMessage.'."}';die;
}

if (!isset($_SERVER['REMOTE_ADDR'])) {
    http_response_code(404);
}

header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json; charset=utf-8');

if ('POST' !== $_SERVER['REQUEST_METHOD']) {
    return404('Invalid request method');
}

foreach (array('username','password') as $value) {
    if (!isset($_POST[$value]) || empty($_POST[$value])) {
        return404('Missing required parameters');
    }
}

define('REDIS_HOST','127.0.0.1');
define('REDIS_PORT','6379');
define('EXPIRY_TIME',3600);

try {
    $redis = new Redis();
    $redis->connect(REDIS_HOST, REDIS_PORT, 1, NULL, 100);
    $redis->ping();
} catch (Exception $e) {
    return501('Unable to connect to cache server');
}

// Redis - one can find the userID from username.
if ($redis->exists('ids_' . $_POST['username'])) {
    $userIdDetails = json_decode($redis->get('ids_' . $_POST['username']), true);
    $userID = $userIdDetails['user_id'];
    $groupID = $userIdDetails['group_id'];
} else {
    return404('Invalid credentials');
}

if (empty($userID) || empty($groupID)) {
    return404('Invalid credentials');
}

// Compare password with hash stored in redis.
// The hash and userIDs are cached in Redis with no expiry.
// Redis stores the hash to handle the load if attack occurs.
if (password_verify($_POST['password'], $redis->get('hash_' . $userID))) { // get hash from redis and compares with password
    $timestamp = time();
    // Redis - Check if token was already generated.
    // Token details are stored in token_ and userID combination.
    // This is to avoid(/ have a control) of multiple token generation for same user account.
    if ($redis->exists('token_' . $userID)) {
        $redisToken = $redis->get('token_' . $userID);
    } else {
        //generates a crypto-secure 32 characters long
        while (true) {
            $token = bin2hex(random_bytes(16));
            if (!$redis->exists($token)) {
                $redis->set($token, '{}', EXPIRY_TIME);
                $redisToken = json_encode(['token' => $token, 'timestamp' => $timestamp]);
                // We set this to have a check first if multiple request/attack occurs.
                $redis->set('token_' . $userID, $redisToken, EXPIRY_TIME);
                break;
            }
        }

        try {
            define('MYSQL_HOSTNAME', '127.0.0.1');
            define('MYSQL_USERNAME', 'root');
            define('MYSQL_PASSWORD', '');
            define('MYSQL_DATABASE', 'product_global');
            $connection = new PDO('mysql:host='.MYSQL_HOSTNAME.';dbname='.MYSQL_DATABASE, MYSQL_USERNAME, MYSQL_PASSWORD, [PDO::ATTR_EMULATE_PREPARES => false]);
        } catch (PDOException $e) {
            return501('Unable to connect to database server');
        }

        $sth = $connection->prepare('SELECT * FROM users u WHERE u.id = ?', array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
        $sth->execute([$userID]);
        $sessRedis = $sth->fetch(PDO::FETCH_ASSOC);
        $sth->closeCursor();
        $connection = null;
    
        $redis->set($token, json_encode($sessRedis), EXPIRY_TIME);
    }

    $tokenDetails = json_decode($redisToken, true);
    echo json_encode(['token' => $tokenDetails['token'], 'expires' => (EXPIRY_TIME - ($timestamp - $tokenDetails['timestamp']))]);
} else {
    return404('Invalid credentials');
}
$redis = null;