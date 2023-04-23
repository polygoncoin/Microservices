<?php
namespace App\Validation;

/**
 * Validator
 *
 * This class is meant for validation
 *
 * @category   Validator
 * @package    Microservices
 * @author     Ramesh Narayan Jangid
 * @copyright  Ramesh Narayan Jangid
 * @version    Release: @1.0.0@
 * @since      Class available since Release 1.0.0
 */
class Validator
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

    /**
     * Validate payload
     *
     * @param array $data             Payload data
     * @param array $validationConfig Validation configuration.
     * @return array
     */
    public function validate(&$data, &$validationConfig)
    {
        $error = [];
        foreach ($validationConfig as &$v) {
            if (!$this->$v['fn']($data[$v['dataKey']])) {
                return $v['errorMessage'];
            }
        }
        return $error;
    }

    private function isAlphanumeric(&$v)
    {
        return preg_match('/^[a-z0-9 .\-]+$/i', $v);
    }
    private function isEmail(&$v)
    {
        return filter_var($v, FILTER_VALIDATE_EMAIL);
    }

    private function validateGroupId($groupId)
    {
        $query = "SELECT COUNT(1) as `count` FROM {$this->db->database}.{$this->groupTable} WHERE id = ?";
        $stmt = $this->db->getStatement($query);
        $stmt->execute([$groupId]);
        return $stmt->fetch(\PDO::FETCH_ASSOC)['count'] === 1;
    }
    
    private function validateClientId($clientId)
    {
        $query = "SELECT COUNT(1) as `count` FROM {$this->db->database}.{$this->clientTable} WHERE id = ?";
        $stmt = $this->db->getStatement($query);
        $stmt->execute([$clientId]);
        return $stmt->fetch(\PDO::FETCH_ASSOC)['count'] === 1;
    }
    
    private function validateRouteId($routeId)
    {
        $query = "SELECT COUNT(1) as `count` FROM {$this->db->database}.{$this->routeTable} WHERE id = ?";
        $stmt = $this->db->getStatement($query);
        $stmt->execute([$routeId]);
        return $stmt->fetch(\PDO::FETCH_ASSOC)['count'] === 1;
    }

    private function validateHttpId($httpId)
    {
        $query = "SELECT COUNT(1) as `count` FROM {$this->db->database}.{$this->httpTable} WHERE id = ?";
        $stmt = $this->db->getStatement($query);
        $stmt->execute([$httpId]);
        return $stmt->fetch(\PDO::FETCH_ASSOC)['count'] === 1;
    }

}
