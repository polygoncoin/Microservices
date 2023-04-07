<?php
define('REDIS_WRITE_HOSTNAME','127.0.0.1');
define('REDIS_WRITE_PORT','6379');
define('REDIS_WRITE_PASSWORD','foobared');
define('REDIS_EXPIRY',3600);

define('MYSQL_READ_HOSTNAME', '127.0.0.1');
define('MYSQL_READ_USERNAME', 'root');
define('MYSQL_READ_PASSWORD', '');
define('MYSQL_READ_DATABASE', 'global');

define('MYSQL_USER_TABLE', 'm007_master_user');
define('MYSQL_GROUP_TABLE', 'm001_master_group');
define('MYSQL_ROUTE_TABLE', 'm004_master_route');

function return404($errMessage)
{
    die('{"status":404,"message":"'.$errMessage.'."}');
}
function return501($errMessage)
{
    die('{"status":501,"message":"'.$errMessage.'."}');
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

$supportedLoad = [
    'all',
    'user',
    'group'
];

if (empty($_GET['load']) || !in_array($_GET['load'], $supportedLoad)) {
    $_GET['load'] = 'all';
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
    $redis->auth(REDIS_PASSWORD);
    if (!$redis->ping()) {
        return501('Unable to ping to cache server');
    }
} catch (Exception $e) {
    return501('Unable to connect to cache server');
}

try {
    $connection = new PDO('mysql:host='.MYSQL_READ_HOSTNAME.';dbname='.MYSQL_READ_DATABASE, MYSQL_READ_USERNAME, MYSQL_READ_PASSWORD, [PDO::ATTR_EMULATE_PREPARES => false]);
} catch (PDOException $e) {
    return501('Unable to connect to database server');
}

$where = '';
if ($_GET['load'] !== 'all' && count($idsArray) > 0) {
    $where = ' WHERE id IN (' . implode($idsArray) . ');';
} else {
    $where = ';';
}

switch ($_GET['load']) {
    case 'all':
    case 'user':
        $sth = $connection->prepare('SELECT * FROM ' . MYSQL_USER_TABLE . $where, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
        $sth->execute();
        while($row =  $sth->fetch(PDO::FETCH_ASSOC)) {
            $allowedIpsArray = [];
            $count = 0;
            if (!empty(trim($row['allowed_ips']))) {
                foreach (explode(',', $row['allowed_ips']) as $cidr) {
                    $cidr = str_replace(' ', '', trim($cidr));
                    if (!empty(trim($cidr))) {
                        $allowedIpsArray[$count++] = ipRange($cidr);
                    }
                }
            }
            if (count($allowedIpsArray) !== 0) {
                $row['allowed_ips'] = json_encode($allowedIpsArray);
                $redis->set('user_ips_' . $row['id'], json_encode($allowedIpsArray));
            }
            $redis->set($row['username'], $row);
        }
        $sth->closeCursor();
        if ($_GET['load'] !== 'all') break;
    case 'all':
    case 'group':
        $sth = $connection->prepare('SELECT id, allowed_ips FROM ' . MYSQL_GROUP_TABLE . $where, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
        $sth->execute();
        while($row =  $sth->fetch(PDO::FETCH_ASSOC)) {
            $allowedIpsArray = [];
            $count = 0;
            if (!empty(trim($row['allowed_ips']))) {
                foreach (explode(',', $row['allowed_ips']) as $cidr) {
                    $cidr = str_replace(' ', '', trim($cidr));
                    if (!empty(trim($cidr))) {
                        $allowedIpsArray[$count++] = ipRange($cidr);
                    }
                }
            }
            if (count($allowedIpsArray) !== 0) {
                $redis->set('group_ips_' . $row['id'], json_encode($allowedIpsArray));
            }
        }
        $sth->closeCursor();
        if ($_GET['load'] !== 'all') break;
    case 'all':
    case 'route':
        $sth = $connection->prepare('SELECT * FROM ' . MYSQL_ROUTE_TABLE . $where, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
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
        if ($_GET['load'] !== 'all') break;
    case default:
        break;
}

$connection = null;
$redis = null;
echo 'done';
