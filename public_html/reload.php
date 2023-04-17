<?php
/*
MIT License 

Copyright (c) 2023 Ramesh Narayan Jangid. 

Permission is hereby granted, free of charge, to any person obtaining a copy 
of this software and associated documentation files (the "Software"), to deal 
in the Software without restriction, including without limitation the rights 
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell 
copies of the Software, and to permit persons to whom the Software is 
furnished to do so, subject to the following conditions: 

The above copyright notice and this permission notice shall be included in all 
copies or substantial portions of the Software. 

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR 
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, 
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE 
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER 
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, 
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE 
SOFTWARE. 
*/
define('__DOC_ROOT__', realpath(__DIR__ . '/../'));
require_once __DOC_ROOT__ . '/public_html/Includes/Reload.php';

header('Content-Type: application/json; charset=utf-8');

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
}

$connection = null;
$redis = null;
echo 'done';
