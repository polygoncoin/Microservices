<?php
namespace App;

use App\Authorize;
use App\JsonEncode;

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
class Reload extends Authorize
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
    function process($refresh, $idsString = null)
    {
        $ids = [];
        if (!is_null($idsString)) {
            foreach (explode(',', trim($idsString)) as $value) {
                if (ctype_digit($value = trim($value))) {
                    $ids[] = (int)$value;
                } else {
                    HttpErrorResponse::return404('Only integer values supported for ids.');
                }
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