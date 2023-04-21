<?php
namespace App\Validation;

/**
 * Validate
 *
 * This class is meant for validation
 *
 * @category   Validate
 * @package    Microservices
 * @author     Ramesh Narayan Jangid
 * @copyright  Ramesh Narayan Jangid
 * @version    Release: @1.0.0@
 * @since      Class available since Release 1.0.0
 */
class Validate
{
    private $db = null;
    private $linkTable = 'l001_link_allowed_route';
    private $groupTable = 'm001_master_group';
    private $userTable = 'm002_master_user';
    private $routeTable = 'm003_master_route';
    private $httpTable = 'm005_master_http';
    private $clientTable = 'm006_master_client';

    public function __construct(&$db = null)
    {
        $this->db = $db;
    }

    public function validateGroupId($groupId)
    {
        $query = "SELECT COUNT(1) as `count` FROM {$this->db->database}.{$this->groupTable} WHERE id = ?";
        $stmt = $this->db->getStatement($query);
        $stmt->execute([$groupId]);
        return $stmt->fetch(\PDO::FETCH_ASSOC)['count'] === 1;
    }
    
    public function validateClientId($clientId)
    {
        $query = "SELECT COUNT(1) as `count` FROM {$this->db->database}.{$this->clientTable} WHERE id = ?";
        $stmt = $this->db->getStatement($query);
        $stmt->execute([$clientId]);
        return $stmt->fetch(\PDO::FETCH_ASSOC)['count'] === 1;
    }
    
    public function validateRouteId($routeId)
    {
        $query = "SELECT COUNT(1) as `count` FROM {$this->db->database}.{$this->routeTable} WHERE id = ?";
        $stmt = $this->db->getStatement($query);
        $stmt->execute([$routeId]);
        return $stmt->fetch(\PDO::FETCH_ASSOC)['count'] === 1;
    }

    public function validateHttpId($httpId)
    {
        $query = "SELECT COUNT(1) as `count` FROM {$this->db->database}.{$this->httpTable} WHERE id = ?";
        $stmt = $this->db->getStatement($query);
        $stmt->execute([$httpId]);
        return $stmt->fetch(\PDO::FETCH_ASSOC)['count'] === 1;
    }

}
