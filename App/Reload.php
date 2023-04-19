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
     * Global DB user table
     *
     * @var string
     */
    public $tableUser = 'm002_master_user';

    /**
     * Global DB group table
     *
     * @var string
     */
    public $tableGroup = 'm001_master_group';

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

    /**
     * Adds user details to cache.
     *
     * @param array $ids Optional - privide ids are specific reload.
     * @return void
     */
    function processUser($ids = [])
    {
        $whereClause = count($ids) ? 'WHERE id IN (' . implode(', ',array_map(function ($id) { return '?';}, $ids)) . ');' : ';';

        $sth = $this->conn->select("SELECT * FROM {$this->db}.{$this->$tableUser} {$where}", array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
        $sth->execute($ids);
        while($row =  $sth->fetch(PDO::FETCH_ASSOC)) {
            $redis->set('user:'.$row['username'], $row);
        }
        $sth->closeCursor();
    }

    /**
     * Adds restricted ips for group members to cache.
     *
     * @param array $ids Optional - privide ids are specific reload.
     * @return void
     */
    function processGroupIps($ids = [])
    {
        $whereClause = count($ids) ? 'WHERE id IN (' . implode(', ',array_map(function ($id) { return '?';}, $ids)) . ');' : ';';

        $sth = $this->conn->select(
            "SELECT id, allowed_ips FROM {$this->db}.{$this->$tableGroup} {$whereClause}",
            array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY)
        );
        $sth->execute($ids);
        $allowedIpsArray = [];
        while($row =  $sth->fetch(PDO::FETCH_ASSOC)) {
            $count = 0;
            if (!empty(trim($row['allowed_ips']))) {
                foreach (explode(',', $row['allowed_ips']) as $cidr) {
                    $cidr = str_replace(' ', '', trim($cidr));
                    if (!empty(trim($cidr))) {
                        $allowedIps = $this->getIpRange($cidr);
                        $allowedIpsArray = array_merge(
                            $allowedIpsArray,
                            range($allowedIps['start'], $allowedIps['end'])
                        );
                    }
                }
            }
            if (count($allowedIpsArray) !== 0) {
                $this->conn->setCache("group:{$row['id']}:ips", $allowedIpsArray);
            }
        }
        $sth->closeCursor();
    }

    /**
     * Adds group allowed routes to cache.
     *
     * @param array $ids Optional - privide ids are specific reload.
     * @return void
     */
    function processGroupMethodRoute($ids = [])
    {
        $whereClause = count($ids) ? 'WHERE L.group_id IN (' . implode(', ',array_map(function ($id) { return '?';}, $ids)) . ');' : ';';

        $sth = $this->conn->select(
            "
                SELECT
                    L.group_id, R.route, 
                FROM 
                    l001_link_allowed_route L
                LEFT JOIN
                    m003_master_route R ON L.route_id = R.id
                $whereClause
            ",
            array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY)
        );
        $sth->execute($ids);
        $routeArr = [];
        while ($row =  $sth->fetch(PDO::FETCH_ASSOC)) {
            if (!isset($routeArr[$row['group_id']])) $routeArr[$row['group_id']] = [];
            $routeArr[$row['group_id']][] = $row['route'];
        }
        $sth->closeCursor();
        foreach ($routeArr as $groupId => &$routes) {
            $redis->setSetMembers("group:{$groupId}:routes", $routes);
        }
    }

    /**
     * Get Ip range from cidr
     *
     * @param string $cidr Eg. 127.0.0.0/24 OR 127.0.0.1
     * @return void
     */
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