<?php
namespace App;

use App\HttpRequest;
use App\HttpErrorResponse;
use App\Connection;
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
/**
 * Updates cache
 *
 * This class is Reloads the Cache values of respective keys
 *
 * @category   Reload
 * @package    Microservices
 * @author     Ramesh Narayan Jangid
 * @copyright  Ramesh Narayan Jangid
 * @version    Release: @1.0.0@
 * @since      Class available since Release 1.0.0
 */
class Reload extends HttpRequest
{
    /**
     * Server connection object
     *
     * @var object
     */
    public $conn = null;

    /**
     * Global DB
     *
     * @var string
     */
    public $db = 'global';

    /**
     * Initialize authorization
     *
     * @return void
     */
    public static function init()
    {
        $this->conn = new Connection();
        $this->process();
    }

    /**
     * Process authorization
     *
     * @return void
     */
    function process($refresh)
    {
        $ids = null;
        foreach (explode(',', trim($_GET['ids'])) as $value) {
            if (ctype_digit($value = trim($value))) {
                $ids[] = (int)$value;
            } else {
                HttpErrorResponse::return404('Only integer values supported for ids.');
            }
        }
        
        switch ($refresh) {
            case 'user':
                $this->processUser($ids);
                break;
            case 'group':
                $this->processGroupMethodRoute($ids);
                break;
        }
    }

    function processUser($ids = null)
    {
        $whereClause = $ids ? 'WHERE id IN (' . implode(', ', $ids) . ');' : ';';

        $sth = $this->conn->select('SELECT * FROM ' . MYSQL_USER_TABLE . $where, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
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
    }

    function processGroupIps($ids = null)
    {
        $whereClause = $ids ? 'WHERE id IN (' . implode(', ', $ids) . ');' : ';';

        $sth = $this->conn->select('SELECT id, allowed_ips FROM ' . MYSQL_GROUP_TABLE . $where, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
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
    }

    function processGroupMethodRoute($ids = null)
    {
        $whereClause = $ids ? 'WHERE id IN (' . implode(', ', $ids) . ');' : ';';

        $sth = $this->conn->select('SELECT * FROM ' . MYSQL_ROUTE_TABLE . $where, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
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
    }

    private function getIpRange($cidr)
    {
        $range = [];
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

    /**
     * Destructor
     */
    public function __destruct()
    {
        
    }
}