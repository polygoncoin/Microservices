<?php
namespace App;

use App\Servers\Cache;
use App\Servers\Database;
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
class Reload
{
    /**
     * Cache Server connection object
     *
     * @var object
     */
    private $cache = null;

    /**
     * Database Server connection object
     *
     * @var object
     */
    private $db = null;

    /**
     * Global DB
     *
     * @var string
     */
    private $globalDb = 'global';

    /**
     * Global DB group table
     *
     * @var string
     */
    private $tableGroup = 'm001_master_group';

    /**
     * Global DB user table
     *
     * @var string
     */
    private $tableUser = 'm002_master_user';

    /**
     * Initialize authorization
     *
     * @return void
     */
    public static function init()
    {
        (new self)->process();
    }

    /**
     * Process authorization
     *
     * @return void
     */
    private function process($refresh = 'all', $idsString = null)
    {
        $this->cache = new Cache();
        $this->db = new Database();

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
        if ($refresh === 'all') {
            $this->processUser();
            $this->processGroup();
            $this->processGroupIps();
            $this->processGroupClientRoutes();
        } else {
            switch ($refresh) {
                case 'user':
                    $this->processUser($ids);
                    break;
                case 'user':
                    $this->processGroup($ids);
                    break;
                case 'groupIp':
                    $this->processGroupIps($ids);
                    break;
                case 'groupRoute':
                    $this->processGroupClientRoutes($ids);
                    break;
            }
        }
    }

    /**
     * Adds user details to cache.
     *
     * @param array $ids Optional - privide ids are specific reload.
     * @return void
     */
    private function processUser($ids = [])
    {
        $whereClause = count($ids) ? 'WHERE id IN (' . implode(', ',array_map(function ($id) { return '?';}, $ids)) . ');' : ';';

        $sth = $this->db->select("
            SELECT
                id,
                username,
                password_hash,
                group_id
            FROM
                {$this->globalDb}.{$this->tableUser}
            {$whereClause}",
            array(\PDO::ATTR_CURSOR => \PDO::CURSOR_FWDONLY));
        $sth->execute($ids);
        while($row =  $sth->fetch(\PDO::FETCH_ASSOC)) {
            $sth1 = $this->db->select("SELECT client_id FROM {$this->globalDb}.{$this->tableUser} WHERE group_id = ?", array(\PDO::ATTR_CURSOR => \PDO::CURSOR_FWDONLY));
            $sth1->execute($row['group_id']);
            $clientIds =  array_column($sth1->fetchAll(\PDO::FETCH_ASSOC), 'client_id');
            $sth1->closeCursor();
            $row = array_merge($row, ['client_ids' => $clientIds]);
            $this->cache->set("user:{$row['username']}", $row);
        }
        $sth->closeCursor();
    }

    /**
     * Adds group details to cache.
     *
     * @param array $ids Optional - privide ids are specific reload.
     * @return void
     */
    private function processGroup($ids = [])
    {
        $whereClause = count($ids) ? 'WHERE G.id IN (' . implode(', ',array_map(function ($id) { return '?';}, $ids)) . ');' : ';';

        $sth = $this->db->select("
            SELECT
                G.id,
                G.name,
                C.db_hostname,
                C.db_username,
                C.db_password,
                C.db_database                
            FROM
                {$this->globalDb}.{$this->tableGroup} G
            LEFT JOIN
                {$this->globalDb}.m004_master_connection C on g.connection_id = C.id
            {$whereClause}",
            array(\PDO::ATTR_CURSOR => \PDO::CURSOR_FWDONLY)
        );
        $sth->execute($ids);
        while($row =  $sth->fetch(\PDO::FETCH_ASSOC)) {
            $this->cache->set("group:{$row['id']}", $row);
        }
        $sth->closeCursor();
    }

    /**
     * Adds restricted ips for group members to cache.
     *
     * @param array $ids Optional - privide ids are specific reload.
     * @return void
     */
    private function processGroupIps($ids = [])
    {
        $whereClause = count($ids) ? 'WHERE id IN (' . implode(', ',array_map(function ($id) { return '?';}, $ids)) . ');' : ';';

        $sth = $this->db->select(
            "SELECT id, allowed_ips FROM {$this->globalDb}.{$this->tableGroup} {$whereClause}",
            array(\PDO::ATTR_CURSOR => \PDO::CURSOR_FWDONLY)
        );
        $sth->execute($ids);
        $allowedIpsArray = [];
        while($row =  $sth->fetch(\PDO::FETCH_ASSOC)) {
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
                $this->cache->setCache("group:{$row['id']}:ips", $allowedIpsArray);
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
    private function processGroupClientRoutes($ids = [])
    {
        $whereClause = count($ids) ? 'WHERE L.group_id IN (' . implode(', ',array_map(function ($id) { return '?';}, $ids)) . ');' : ';';

        $sth = $this->db->select(
            "
                SELECT
                    L.group_id, L.client_id, R.route, 
                FROM 
                    {$this->globalDb}.l001_link_allowed_route L
                LEFT JOIN
                    {$this->globalDb}.m003_master_route R ON L.route_id = R.id
                {$whereClause}
            ",
            array(\PDO::ATTR_CURSOR => \PDO::CURSOR_FWDONLY)
        );
        $sth->execute($ids);
        $routeArr = [];
        while ($row =  $sth->fetch(\PDO::FETCH_ASSOC)) {
            if (!isset($routeArr[$row['group_id']])) $routeArr[$row['group_id']] = [];
            $routeArr[$row['group_id']][$row['client_id']][] = $row['route'];
        }
        $sth->closeCursor();
        foreach ($routeArr as $groupId => &$arr) {
            foreach ($arr as $clientId => &$routes) {
                $key = "group:{$groupId}:client:{$clientId}:routes";
                $this->cache->setSetMembers($key, $routes);
            }
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
        $jsonEncode = new JsonEncode();
        $jsonEncode->encode(
            [
                'Status' => 200,
                'Message' => 'Successfully added details to Cache Server.'
            ]
        );
        $jsonEncode = null;
    }
}