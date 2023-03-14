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

define('REDIS_LOCAL_HOSTNAME', '127.0.0.1');
define('REDIS_LOCAL_DATABASE',  NULL);
define('REDIS_LOCAL_PORT_NUMBER', '6379');
define('EXPIRY_TIME', 3600);

try {
    $redisLocal = new Redis();
    $redisLocal->pconnect(REDIS_LOCAL_HOSTNAME, REDIS_LOCAL_PORT_NUMBER, 1, REDIS_LOCAL_DATABASE, 100);
    $redisLocal->ping();
} catch (Exception $e) {
    return501('Unable to connect to cache server');
}

// Redis - one can find the userID from username.
if ($redisLocal->exists($_POST['username'])) {
    $userDetails = json_decode($redisLocal->get($_POST['username']), true);
    if ($redisLocal->exists($_POST['username'])) {
        $allowedIps = $redisLocal->get('allowed_ips_' . $userDetails['group_id']);
        
    }
} else {
    return404('Invalid credentials.');
}

if (empty($userDetails['id']) || empty($userDetails['group_id'])) {
    return404('Invalid credentials');
}

// Compare password with hash stored in redis.
// The hash and userIDs are cached in Redis with no expiry.
// Redis stores the hash to handle the load if attack occurs.
if (password_verify($_POST['password'], $userDetails['password_hash']))) { // get hash from redis and compares with password
    $timestamp = time();
    // Redis - Check if token was already generated.
    // Token details are stored in token_ and userID combination.
    // This is to avoid(/ have a control) of multiple token generation for same user account.
    if ($redisLocal->exists('token_' . $userDetails['id'])) {
        $redisToken = $redisLocal->get('token_' . $userDetails['id']);
    } else {
        try {
            $redisToken = new Redis();
            $redisToken->connect(REDIS_TOKEN_HOSTNAME, REDIS_TOKEN_PORT_NUMBER, 1, REDIS_TOKEN_DATABASE, 100);
            $redisToken->ping();
        } catch (Exception $e) {
            return501('Unable to connect to cache server');
        }
        //generates a crypto-secure 32 characters long
        while (true) {
            $token = bin2hex(random_bytes(16));
            if (!$redisToken->exists($token)) {
                $redisToken->set($token, '{}', EXPIRY_TIME);
                $redisToken = json_encode(['token' => $token, 'timestamp' => $timestamp]);
                break;
            }
        }
        // We set this to have a check first if multiple request/attack occurs.
        $redisLocal->set('token_' . $userDetails['id'], $redisToken, EXPIRY_TIME);

        try {
            define('MYSQL_READ_HOSTNAME', '127.0.0.1');
            define('MYSQL_READ_USERNAME', 'root');
            define('MYSQL_READ_PASSWORD', '');
            define('MYSQL_READ_DATABASE', 'product_global');
            $connection = new PDO('mysql:host='.MYSQL_READ_HOSTNAME.';dbname='.MYSQL_READ_DATABASE, MYSQL_READ_USERNAME, MYSQL_READ_PASSWORD, [PDO::ATTR_EMULATE_PREPARES => false]);
        } catch (PDOException $e) {
            return501('Unable to connect to database server');
        }

        $sth = $connection->prepare('SELECT * FROM m009_master_user m009 WHERE m009.id = ?', array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
        $sth->execute([$userDetails['id']]);
        $sessRedis = $sth->fetch(PDO::FETCH_ASSOC);
        $sth->closeCursor();
        $connection = null;
    
        $redisToken->set($token, json_encode($sessRedis), EXPIRY_TIME);
    }

    $tokenDetails = json_decode($redisToken, true);
    echo json_encode(['token' => $tokenDetails['token'], 'expires' => (EXPIRY_TIME - ($timestamp - $tokenDetails['timestamp']))]);
} else {
    return404('Invalid credentials');
}
