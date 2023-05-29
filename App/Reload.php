<?php
namespace App;

use App\Servers\Cache;
use App\Servers\Database;
use App\JsonEncode;
use App\PHPTrait;

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
    use PHPTrait;
    
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
        $this->cache = new Cache(
            'cacheHostname',
            'cachePort',
            'cachePassword'
        );
        $this->db = new Database(
            'dbHostnameDefault',
            'dbUsernameDefault',
            'dbPasswordDefault',
            'globalDbName'
        );

        $ids = [];
        if (!is_null($idsString)) {
            foreach (explode(',', trim($idsString)) as $value) {
                if (ctype_digit($value = trim($value))) {
                    $ids[] = (int)$value;
                } else {
                    HttpErrorResponse::return4xx(404, 'Only integer values supported for ids.');
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
                case 'token':
                    $this->processToken($idsString);
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
        $whereClause = count($ids) ? 'WHERE U.id IN (' . implode(', ',array_map(function ($id) { return '?';}, $ids)) . ');' : ';';

        try {
            $stmt = $this->db->getStatement("
                SELECT
                    id,
                    username,
                    password_hash,
                    group_id
                FROM
                    `{$this->execPhpFunc(getenv('users'))}` U
                {$whereClause}",
                array(\PDO::ATTR_CURSOR => \PDO::CURSOR_FWDONLY));
            $stmt->execute($ids);
            $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            $stmt->closeCursor();
        } catch(\PDOException $e) {
            HttpErrorResponse::return5xx(501, 'Database error. ' . $e->getMessage());
        }
        foreach ($rows as &$row) {
            $this->cache->setCache("user:{$row['username']}", json_encode($row));
        }
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
        try {
            $stmt = $this->db->getStatement("
                SELECT
                    G.id,
                    G.name,
                    G.client_id,
                    C.db_hostname,
                    C.db_username,
                    C.db_password,
                    C.db_database                
                FROM
                    `{$this->execPhpFunc(getenv('groups'))}` G
                LEFT JOIN
                    `{$this->execPhpFunc(getenv('connections'))}` C on g.connection_id = C.id
                {$whereClause}",
                array(\PDO::ATTR_CURSOR => \PDO::CURSOR_FWDONLY)
            );
            $stmt->execute($ids);
        } catch(\PDOException $e) {
            HttpErrorResponse::return5xx(501, 'Database error. ' . $e->getMessage());
        }
        while($row =  $stmt->fetch(\PDO::FETCH_ASSOC)) {
            $this->cache->setCache("group:{$row['id']}", json_encode($row));
        }
        $stmt->closeCursor();
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
        try {
            $stmt = $this->db->getStatement(
                "SELECT id, allowed_ips FROM `{$this->execPhpFunc(getenv('groups'))}` {$whereClause}",
                array(\PDO::ATTR_CURSOR => \PDO::CURSOR_FWDONLY)
            );
            $stmt->execute($ids);
        } catch(\PDOException $e) {
            HttpErrorResponse::return5xx(501, 'Database error. ' . $e->getMessage());
        }
        $allowedIpsArray = [];
        while($row =  $stmt->fetch(\PDO::FETCH_ASSOC)) {
            $count = 0;
            if (!empty($row['allowed_ips'])) {
                foreach (explode(',', trim($row['allowed_ips'])) as $cidr) {
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
                $this->cache->setSetMembers("group:{$row['id']}:ips", $allowedIpsArray);
            }
        }
        $stmt->closeCursor();
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
        try {
            $stmt = $this->db->getStatement(
                "
                    SELECT
                        L.group_id, L.http_id, R.route
                    FROM 
                        `{$this->execPhpFunc(getenv('links'))}` L
                    LEFT JOIN
                        `{$this->execPhpFunc(getenv('routes'))}` R ON L.route_id = R.id
                    {$whereClause}
                ",
                array(\PDO::ATTR_CURSOR => \PDO::CURSOR_FWDONLY)
            );
            $stmt->execute($ids);
        } catch(\PDOException $e) {
            HttpErrorResponse::return5xx(501, 'Database error. ' . $e->getMessage());
        }
        $routeArr = [];
        while ($row =  $stmt->fetch(\PDO::FETCH_ASSOC)) {
            if (!isset($routeArr[$row['group_id']])) $routeArr[$row['group_id']] = [];
            if (!isset($routeArr[$row['group_id']][$row['http_id']])) $routeArr[$row['group_id']][$row['http_id']] = [];
            $routeArr[$row['group_id']][$row['http_id']][] = $row['route'];
        }
        $stmt->closeCursor();
        foreach ($routeArr as $groupId => &$clientArr) {
            foreach ($clientArr as $clientId => &$httpArr) {
                foreach ($httpArr as $httpId => &$routes) {
                    $key = "group:{$groupId}:http:{$httpId}:routes";
                    $this->cache->setSetMembers($key, $routes);
                }
            }
        }
    }

    /**
     * Remove token from cache.
     *
     * @param string $token Token to be delete from cache.
     * @return void
     */
    private function processToken($token)
    {
        $this->cache->deleteCache($token);
    }

    /**
     * Get Ip range from cidr
     *
     * @param string $cidr Eg. 127.0.0.0/24 OR 127.0.0.1
     * @return void
     */
    private function getIpRange($cidr)
    {echo __FUNCTION__;
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
