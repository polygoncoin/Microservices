<?php
define('REDIS_HOST','127.0.0.1');
define('REDIS_PORT','6379');
define('EXPIRY_TIME',3600);

define('MYSQL_HOSTNAME', '127.0.0.1');
define('MYSQL_USERNAME', 'root');
define('MYSQL_PASSWORD', '');
define('MYSQL_DATABASE', 'product_global');

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
header('Content-Type: application/json; charset=utf-8');

$supportedReload = [
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
    return404('Invalid request.');
}

if (empty($_GET['ids'])) {
    return404('Invalid request.');
}

$idsArray = explode(',', $_GET['ids']);
foreach ($idsArray as &$value) {
    if (ctype_digit($$value)) {
        $value = (int)$value;
    } else {
        return404('Only integer values supported for ids.');
    }
}

try {
    $redis = new Redis();
    $redis->connect(REDIS_HOST, REDIS_PORT, 1, NULL, 100);
    $redis->ping();
} catch (Exception $e) {
    return501('Unable to connect to cache server');
}

try {
    $connection = new PDO('mysql:host='.MYSQL_HOSTNAME.';dbname='.MYSQL_DATABASE, MYSQL_USERNAME, MYSQL_PASSWORD, [PDO::ATTR_EMULATE_PREPARES => false]);
} catch (PDOException $e) {
    return501('Unable to connect to database server');
}


$where = '';
if (count($idsArray) > 0) {
    $where = ' WHERE id IN (' . implode($idsArray) . ');';
} else {
    $where = ';';
}

switch ($_GET['reload']) {
    case 'ids':
        $sth = $connection->prepare('SELECT id, username, group_id FROM m008_master_user' . $where, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
        $sth->execute();
        while($row =  $sth->fetch(PDO::FETCH_ASSOC)) {
            $redis->set('ids_' . $row['username'], json_encode(['user_id' => $row['id'], 'group_id' => $row['group_id']]));
        }
        $sth->closeCursor();
        break;
    case 'hash':
        $sth = $connection->prepare('SELECT id, password FROM m008_master_user' . $where, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
        $sth->execute();
        while($row =  $sth->fetch(PDO::FETCH_ASSOC)) {
            $redis->set('hash_' . $row['id'], $row['password']);
        }
        $sth->closeCursor();
        break;
    case 'group_ips':
        $sth = $connection->prepare('SELECT id, allowed_ips FROM m000_master_group' . $where, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
        $sth->execute();
        while($row =  $sth->fetch(PDO::FETCH_ASSOC)) {
            if (!empty(trim($row['allowed_ips']))) {
                $count = 0;
                $allowedIpsArray = [];
                foreach (explode(',', $row['allowed_ips']) as $cidr) {
                    $cidr = str_replace(' ', '', trim($cidr));
                    if (!empty(trim($cidr))) {
                        $allowedIpsArray[$count++] = ipRange($cidr);
                    }
                }
                $redis->set('group_ips_' . $row['id'], json_encode($allowedIpsArray));
            }
        }
        $sth->closeCursor();
        break;
    case 'corporate_ips':
        $sth = $connection->prepare('SELECT id, allowed_ips FROM m000_master_corporate' . $where, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
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
                $redis->set('corporate_ips_' . $row['id'], json_encode($allowedIpsArray));
            }
        }
        $sth->closeCursor();
        break;

    case 'company_ips':
        $sth = $connection->prepare('SELECT id, allowed_ips FROM m002_master_company' . $where, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
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
                $redis->set('company_ips_' . $row['id'], json_encode($allowedIpsArray));
            }
        }
        $sth->closeCursor();
        break;
    case 'application_ips':
        $sth = $connection->prepare('SELECT id, allowed_ips FROM m003_master_application' . $where, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
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
                $redis->set('application_ips_' . $row['id'], json_encode($allowedIpsArray));
            }
        }
        $sth->closeCursor();
        break;
    case 'client_ips':
        $sth = $connection->prepare('SELECT id, allowed_ips FROM m009_master_client' . $where, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
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
                $redis->set('client_ips_' . $row['id'], json_encode($allowedIpsArray));
            }
        }
        $sth->closeCursor();
        break;
    case 'module_ips':
        $sth = $connection->prepare('SELECT id, allowed_ips FROM m004_master_module' . $where, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
        $sth->execute();
        while($row =  $sth->fetch(PDO::FETCH_ASSOC)) {
            if (!is_null($row['allowed_ips'])) {
                $allowedIpsArray = [];
                foreach (explode(',', $row['allowed_ips']) as $cidr) {
                    $cidr = str_replace(' ', '', trim($cidr));
                    if (!empty(trim($cidr))) {
                        $allowedIpsArray[$count++] = ipRange($cidr);
                    }
                }
                $redis->set('module_ips_' . $row['id'], json_encode($allowedIpsArray));
            }
        }
        $sth->closeCursor();
        break;
    case 'user_ips':
        $sth = $connection->prepare('SELECT id, allowed_ips FROM user' . $where, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
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
        break;
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

header('Content-Type: application/json; charset=utf-8');
echo '{"status" : 200, "message" : "Successfully done"}';
