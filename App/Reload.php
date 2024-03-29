<?php
namespace App;

use App\Servers\Cache\Cache;
use App\Servers\Database\Database;
use App\JsonEncode;
use App\AppTrait;

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
    use AppTrait;
    
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
        switch (true) {
            case isset($_GET['refresh']) && isset($_GET['ids']):
                (new self)->process($_GET['refresh'], $_GET['ids']);
                break;       
            case isset($_GET['refresh']):
                (new self)->process($_GET['refresh']);
                break;       
            default:
                (new self)->process();
                break;       
        }
    }

    /**
     * Process authorization
     *
     * @return void
     */
    private function process($refresh = 'all', $idsString = null)
    {
        Cache::connect(
            'Redis',
            'cacheHostname',
            'cachePort',
            'cachePassword',
            'cacheDatabase'
        );
        $this->cache = Cache::getObject();
        Database::connect(
            'MySQL',
            'dbHostnameDefault',
            'dbUsernameDefault',
            'dbPasswordDefault',
            'globalDbName'
        );
        $this->db = Database::getObject();
        $ids = [];
        if (!is_null($idsString)) {
            foreach (explode(',', trim($idsString)) as $value) {
                if (ctype_digit($value = trim($value))) {
                    $ids[] = (int)$value;
                } else {
                    HttpResponse::return4xx(404, 'Only integer values supported for ids.');
                }
            }
        }
        if ($refresh === 'all') {
            $this->processUser();
            $this->processGroup();
            $this->processGroupIps();
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
        $whereClause = count($ids) ? 'WHERE U.user_id IN (' . implode(', ',array_map(function ($id) { return '?';}, $ids)) . ');' : ';';

        $this->db->execDbQuery("
            SELECT
                U.user_id,
                U.username,
                U.password_hash,
                G.client_id,
                G.name as group_name,
                U.group_id
            FROM
                `{$this->execPhpFunc(getenv('users'))}` U
            LEFT JOIN
                `{$this->execPhpFunc(getenv('groups'))}` G ON U.group_id = G.group_id
            {$whereClause}", $ids);
        $rows = $this->db->fetchAll(\PDO::FETCH_ASSOC);
        $this->db->closeCursor();
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
        $whereClause = count($ids) ? 'WHERE G.group_id IN (' . implode(', ',array_map(function ($id) { return '?';}, $ids)) . ');' : ';';
        $this->db->execDbQuery("
            SELECT
                G.group_id,
                G.name,
                G.client_id,
                C.db_server_type,
                C.db_hostname,
                C.db_username,
                C.db_password,
                C.db_database                
            FROM
                `{$this->execPhpFunc(getenv('groups'))}` G
            LEFT JOIN
                `{$this->execPhpFunc(getenv('connections'))}` C on G.connection_id = C.connection_id
            {$whereClause}", $ids);
        while($row =  $this->db->fetch(\PDO::FETCH_ASSOC)) {
            $this->cache->setCache("group:{$row['group_id']}", json_encode($row));
        }
        $this->db->closeCursor();
    }

    /**
     * Adds restricted ips for group members to cache.
     *
     * @param array $ids Optional - privide ids are specific reload.
     * @return void
     */
    private function processGroupIps($ids = [])
    {
        $whereClause = count($ids) ? 'WHERE group_id IN (' . implode(', ',array_map(function ($id) { return '?';}, $ids)) . ');' : ';';
        $this->db->execDbQuery(
            "SELECT group_id, allowed_ips FROM `{$this->execPhpFunc(getenv('groups'))}` {$whereClause}",
            $ids
        );
        $cidrArray = [];
        while($row =  $this->db->fetch(\PDO::FETCH_ASSOC)) {
            if (!empty($row['allowed_ips'])) {
                $cidr = json_decode(trim($cidr), true);
                if (count($cidr)>0) {
                    $this->cache->setCache("group:{$row['group_id']}:cidr", json_encode($cidr));

                }
            }
        }
        $this->db->closeCursor();
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
     * Destructor
     */
    public function __destruct()
    {
    }
}
