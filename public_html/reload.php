<?php
define('REDIS_HOST','127.0.0.1');
define('REDIS_PORT','6379');
define('EXPIRY_TIME',3600);

define('MYSQL_READ_HOSTNAME', '127.0.0.1');
define('MYSQL_READ_USERNAME', 'root');
define('MYSQL_READ_PASSWORD', '');
define('MYSQL_READ_DATABASE', 'product_global');

function return404($errMessage)
{
    echo '{"status":404,"message":"'.$errMessage.'."}';die;
}
function return501($errMessage)
{
    echo '{"status":501,"message":"'.$errMessage.'."}';die;
}
function ipRange($cidr)
{
    $range = array();
    $cidr = explode('/', trim($cidr));
    if (count($cidr)===1) {
        $range['start'] = ip2long($cidr[0]);
        $range['end'] = ip2long($cidr[0]);
    } elseif (count($cidr)===2) {
        $range['start'] = ((ip2long($cidr[0])) & ((-1 << (32 - (int)$cidr[1]))));
        $range['end'] = ((ip2long($range[0])) + pow(2, (32 - (int)$cidr[1])) - 1);
    }
    return $range;
}

$supportedReload = [
    'all',
    'ids',
    'hash',
    'group_ips',
    'corporate_ips',
    'company_ips',
    'application_ips',
    'client_ips',
    'module_ips',
    'user_ips',
    'route'
];

if (empty($_GET['reload']) || !in_array($_GET['reload'], $supportedReload)) {
    return404('Invalid request');
}

if (!empty($_GET['ids'])) {
    $idsArray = explode(',', $_GET['ids']);
    foreach ($idsArray as &$value) {
        if (ctype_digit($$value)) {
            $value = (int)$value;
        } else {
            return404('Only integer values supported for ids.');
        }
    }
}

try {
    $redis = new Redis();
    //Connecting to Redis
    $redis->connect(REDIS_HOST, REDIS_PORT, 1, NULL, 100);
//    $redis->auth('password');
//    if ($redis->ping()) {
//        echo "PONG";
//    }
    $redis->ping();
} catch (Exception $e) {
    return501('Unable to connect to cache server');
}

try {
    $connection = new PDO('mysql:host='.MYSQL_READ_HOSTNAME.';dbname='.MYSQL_READ_DATABASE, MYSQL_READ_USERNAME, MYSQL_READ_PASSWORD, [PDO::ATTR_EMULATE_PREPARES => false]);
} catch (PDOException $e) {
    return501('Unable to connect to database server');
}


$where = '';
if ($_GET['reload'] !== 'all' && count($idsArray) > 0) {
    $where = ' WHERE id IN (' . implode($idsArray) . ');';
} else {
    $where = ';';
}

switch ($_GET['reload']) {
    case 'all':
    case 'ids':
        $sth = $connection->prepare('SELECT * FROM m008_master_user' . $where, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
        $sth->execute();
        while($row =  $sth->fetch(PDO::FETCH_ASSOC)) {
            $redis->set($row['username'], $row);
        }
        $sth->closeCursor();
        if ($_GET['reload'] !== 'all') break;
    case 'all':
    case 'allowed_ips':
        $sth = $connection->prepare('SELECT id, corporate_allowed_ips, company_allowed_ips, application_allowed_ips FROM m000_master_group' . $where, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
        $sth->execute();
        while($row =  $sth->fetch(PDO::FETCH_ASSOC)) {
            $allowedIpsArray = [];
            $count = 0;
            if (!empty(trim($row['corporate_allowed_ips']))) {
                foreach (explode(',', $row['corporate_allowed_ips']) as $cidr) {
                    $cidr = str_replace(' ', '', trim($cidr));
                    if (!empty(trim($cidr))) {
                        $allowedIpsArray[$count++] = ipRange($cidr);
                    }
                }
            }
            if (!empty(trim($row['company_allowed_ips']))) {
                foreach (explode(',', $row['company_allowed_ips']) as $cidr) {
                    $cidr = str_replace(' ', '', trim($cidr));
                    if (!empty(trim($cidr))) {
                        $allowedIpsArray[$count++] = ipRange($cidr);
                    }
                }
            }
            if (!empty(trim($row['application_allowed_ips']))) {
                foreach (explode(',', $row['application_allowed_ips']) as $cidr) {
                    $cidr = str_replace(' ', '', trim($cidr));
                    if (!empty(trim($cidr))) {
                        $allowedIpsArray[$count++] = ipRange($cidr);
                    }
                }
            }
            if (count($allowedIpsArray) !== 0) {
                $redis->set('allowed_ips_' . $row['id'], json_encode($allowedIpsArray));
            }
        }
        $sth->closeCursor();
        if ($_GET['reload'] !== 'all') break;
    case 'all':
    case 'user_ips':
        $sth = $connection->prepare('SELECT id, allowed_ips FROM m008_master_user' . $where, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
        $sth->execute();
        while($row =  $sth->fetch(PDO::FETCH_ASSOC)) {
            if (!empty(trim($row['allowed_ips']))) {
                $allowedIpsArray = [];
                foreach (explode(',', $row['allowed_ips']) as $cidr) {
                    $cidr = str_replace(' ', '', trim($cidr));
                    if (!empty(trim($cidr))) {
                        $allowedIpsArray[$count++] = ipRange($cidr);
                    }
                }
                $redis->set('user_allowed_ips_' . $row['id'], json_encode($allowedIpsArray));
            }
        }
        $sth->closeCursor();
        if ($_GET['reload'] !== 'all') break;
    case 'all':
    case 'route':
        $sth = $connection->prepare('SELECT * FROM m007_master_route' . $where, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
        $sth->execute();
        $routeArr = [];
        while ($row =  $sth->fetch(PDO::FETCH_ASSOC)) {
            $routeArr[$row['group_id']][$row['route']][$row['method']] = $row;
        }
        $sth->closeCursor();
        foreach ($routeArr as $groupID => &$arr) {
            foreach ($arr as $route => &$routeDetails) {
                $redis->set($groupID . '_' . base64_encode($route), json_encode($routeDetails));
            }
        }
        break;
}

$connection = null;
$redis = null;

echo 'done';
