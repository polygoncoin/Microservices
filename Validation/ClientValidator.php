<?php
namespace Microservices\Validation;

use Microservices\App\Constants;
use Microservices\App\Env;
use Microservices\App\HttpRequest;
use Microservices\App\HttpResponse;
use Microservices\App\Logs;
use Microservices\App\Servers\Cache\Cache;
use Microservices\App\Servers\Database\Database;
use Microservices\Validation\ValidatorTrait;

/**
 * Validator
 *
 * This class is meant for clients db related validation
 *
 * @category   Client DB Validator
 * @package    Microservices
 * @author     Ramesh Narayan Jangid
 * @copyright  Ramesh Narayan Jangid
 * @version    Release: @1.0.0@
 * @since      Class available since Release 1.0.0
 */
class ClientValidator
{
    use ValidatorTrait;

    /**
     * Database object
     */
    private $db = null;

    public function __construct()
    {
        $this->db = Database::getObject();
    }

    /**
     * Validate payload
     *
     * @param array $input            Input's data
     * @param array $validationConfig Validation configuration.
     * @return array
     */
    public function validate($input, $validationConfig)
    {
        $isValidData = true;
        $errors = [];
        foreach ($validationConfig as &$v) {
            $args = [];
            foreach ($v['fnArgs'] as $attr => list($mode, $key)) {
                if ($mode === 'custom') {
                    $args[$attr] = $key;
                } else {
                    $args[$attr] = $input[$mode][$key];
                }
            }
            $fn = $v['fn'];
            if (!$this->$fn($args)) {
                $errors[] = $v['errorMessage'];
                $isValidData = false;
            }
        }
        return [$isValidData, $errors];
    }

    /**
     * Client Id Exist
     *
     * @param array $args Arguments
     * @return integer 0/1
     */
    public function clientIdExist($args)
    {
        extract($args);
        return $this->getPrimaryCount(Env::$clients, 'client_id', $client_id);
    }

    /**
     * Gets primary key count
     *
     * @param string  $table   Table Name
     * @param string  $primary Primary Key
     * @param integer $id      Primary Id
     * @return integer 0/1
     */
    private function getPrimaryCount($table, $primary, $id)
    {
        $db = Env::$defaultDbDatabase;
        $sql = "SELECT count(1) as `count` FROM `{$db}`.`{$table}` WHERE `{$primary}` = ?";
        $params = [$id];
        $this->db->execDbQuery($sql, $params);
        return ($this->db->fetch())['count'];
    }

    /**
     * Checks primary key exist
     *
     * @param array $args Arguments
     * @return integer 0/1
     */
    private function primaryKeyExist($args)
    {
        extract($args);
        $sql = "SELECT count(1) as `count` FROM `{$table}` WHERE `{$primary}` = ?";
        $params = [$id];
        $this->db->execDbQuery($sql, $params);
        $row = $this->db->fetch();
        $this->db->closeCursor();
        return ($row['count'] === 0) ? false : true;
    }
}
