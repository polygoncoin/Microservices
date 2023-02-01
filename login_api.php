<?php
header('Content-Type: application/json; charset=utf-8');
if ('POST' !== $_SERVER['REQUEST_METHOD']) {
    echo '{"status":404,"message":"HTTP method not supported"}';die;
}
if (!isset($_POST['crud'])) {
    echo '{"status":404,"message":"Missing required params"}';die;
}
if ($_POST['crud'] !== 'token') {
    echo '{"status":404,"message":"Invalid crud"}';die;
}
//$hash = password_hash($password, PASSWORD_DEFAULT);

define('REDIS_HOST','127.0.0.1');
define('REDIS_PORT','6379');
define('EXPIRY_TIME',3600);

$redis = new Redis();
$redis->connect(REDIS_HOST, REDIS_PORT, 1, NULL, 100);

try {
    $redis->ping();
} catch (Exception $e) {
    echo '{"status":404,"message":"REDIS NOT CONNECTED: '.$e->getMessage().'"}';
}
// Redis - one can find the userID from Email.
$user_id = $redis->get('id_'.$_POST['email']);

// Compare password with hash stored in redis.
// The hash and userIDs are cached in Redis with no expiry.
// Redis stores the hash to handle the load if attack occurs.
if (password_verify($_POST['password'], $redis->get('hash_'.$user_id))) { // get hash from redis and compares with password
    // Redis - Check if token was already generated.
    // Token details are stored in token_ and userID combination.
    // This is to avoid(/ have a control) of multiple token generation for same user account.
    if ($redis->exists('token_' . $user_id)) {
        $redisToken = $redis->get('token_' . $user_id);
    } else {
        //generates a crypto-secure 32 characters long
        $token = bin2hex(random_bytes(16));
        $redisToken = json_encode(['token' => $token, 'timestamp' => time()]);
        // We set this to have a check first if multiple request/attack occurs.
        $redis->set('token_' . $user_id, $redisToken, EXPIRY_TIME);
        if (!$redis->exists($token)) {
            try {
                $dbhost = '127.0.0.1';
                $dbname='product_global';
                $dbuser = 'root';
                $dbpass = '';
                $connection = new PDO("mysql:host=$dbhost;dbname=$dbname", $dbuser, $dbpass);
            }catch (PDOException $e) {
                echo "Error : " . $e->getMessage() . "<br/>";
                die();
            }
            $sth = $connection->prepare('SELECT * FROM users u WHERE u.email = ?', array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
            $sth->execute($_POST['email']);
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
            . 'WHERE u.email = ?'."\n"
            . 'ORDER BY crud';
            $sth = $connection->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
            $sth->execute($_POST['email']);
            foreach($sth->fetchAll() as $rows) {
                if (!isset($sessRedis['cruds'][$rows['crud']])) $sessRedis['cruds'][$rows['crud']] = [];
                $sessRedis['cruds'][$rows['crud']][] = $rows['http'];
            }
            $sth->closeCursor();
            //set session details.
            $redis->set($token, json_encode($sessRedis), EXPIRY_TIME);
        }
    }
}
$tokenDetails = json_decode($redisToken, true);
echo json_encode(['token' => $tokenDetails['token'], 'expires' => (time() - $tokenDetails['timestamp'])]);die;