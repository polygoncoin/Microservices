<?php
header('Content-Type: application/json; charset=utf-8');
function return404($errMessage)
{
    echo '{"status":404,"message":"'.$errMessage.'."}';die;
}
function return501($errMessage)
{
    echo '{"status":501,"message":"'.$errMessage.'."}';die;
}
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

$redis = new Redis();
$redis->connect(REDIS_HOST, REDIS_PORT, 1, NULL, 100);
try {
    $redis->ping();
} catch (Exception $e) {
    return501('REDIS NOT CONNECTED');
}
/*
$userID = 1;
$redis->delete('id_' . $_POST['username']);
$redis->delete('hash_' . $userID);
$redis->delete('token_' . $userID);
$redis->set('id_' . $_POST['username'], $userID);
$redis->set('hash_' . $userID, password_hash($_POST['password'], PASSWORD_DEFAULT));
*/
// Redis - one can find the userID from username.
$userID = null;
if ($redis->exists('id_' . $_POST['username'])) {
    $userID = $redis->get('id_' . $_POST['username']);
}
if (empty($userID)) {
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
                $redisToken = json_encode(['token' => $token, 'timestamp' => $timestamp]);
                $redis->set($token, '{}', EXPIRY_TIME);
                // We set this to have a check first if multiple request/attack occurs.
                $redis->set('token_' . $userID, $redisToken, EXPIRY_TIME);
                break;
            }
        }
        try {
            $dbhost = '127.0.0.1';
            $dbname='product_global';
            $dbuser = 'root';
            $dbpass = '';
            $connection = new PDO("mysql:host=$dbhost;dbname=$dbname", $dbuser, $dbpass);
        }catch (PDOException $e) {
            return501('MySql not connected');
        }
        $sth = $connection->prepare('SELECT * FROM users u WHERE u.username = ?', array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
        $sth->execute([$_POST['username']]);
        $sessRedis = [
            'user' => ($sth->fetchAll())[0],
            'cruds' => [],
        ];
        $sth->closeCursor();
        $sql = 'SELECT mbc.name as crud, mhttp.name as http'."\n"
        . 'FROM link_crud_http_group lchg'."\n"
        . 'LEFT JOIN link_crud_http lch ON lchg.link_crud_http_id = lch.id'."\n"
        . 'LEFT JOIN master_bu_crud mbc ON lch.crud_id = mbc.id'."\n"
        . 'LEFT JOIN master_http mhttp on lch.http_id = mhttp.id'."\n"
        . 'LEFT JOIN users u ON lchg.group_id = u.group_id'."\n"
        . 'WHERE u.username = ?'."\n"
        . 'ORDER BY crud';
        $sth = $connection->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
        $sth->execute([$_POST['username']]);
        foreach($sth->fetchAll() as $rows) {
            if (!isset($sessRedis['cruds'][$rows['crud']])) $sessRedis['cruds'][$rows['crud']] = [];
            $sessRedis['cruds'][$rows['crud']][] = $rows['http'];
        }
        $sth->closeCursor();
        //set session details.
        $redis->set($token, json_encode($sessRedis), EXPIRY_TIME);
    }
    $tokenDetails = json_decode($redisToken, true);
    echo json_encode(['token' => $tokenDetails['token'], 'expires' => (EXPIRY_TIME - ($timestamp - $tokenDetails['timestamp']))]);die;
} else {
    return404('Invalid credentials');
}
