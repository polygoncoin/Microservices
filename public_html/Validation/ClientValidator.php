<?php
namespace Microservices\public_html\Validation;

use Microservices\App\Constants;
use Microservices\App\Common;
use Microservices\App\Env;
use Microservices\App\Servers\Database\AbstractDatabase;
use Microservices\public_html\Validation\ValidatorInterface;
use Microservices\public_html\Validation\ValidatorTrait;

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
class ClientValidator implements ValidatorInterface
{
    use ValidatorTrait;

    /**
     * Database Object
     *
     * @var null|AbstractDatabase
     */
    public $db = null;

    /**
     * Microservices Collection of Common Objects
     *
     * @var null|Common
     */
    private $c = null;

    /**
     * Constructor
     *
     * @param Common $common
     */
    public function __construct(&$common)
    {
        $this->c = &$common;
        $this->db = &$this->c->httpRequest->db;
    }

    /**
     * Validate payload
     *
     * @param array $validationConfig Validation configuration
     * @return array
     */
    public function validate(&$validationConfig)
    {
        $session = &$this->c->httpRequest->session;
        $isValidData = true;
        $errors = [];
        foreach ($validationConfig as &$v) {
            $args = [];
            foreach ($v['fnArgs'] as $attr => list($mode, $key)) {
                if ($mode === 'custom') {
                    $args[$attr] = $key;
                } else {
                    $args[$attr] = $session[$mode][$key];
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
    public function clientIdExist(&$args)
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
    private function getPrimaryCount(&$table, &$primary, &$id)
    {
        $db = $this->db->database;
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
    private function primaryKeyExist(&$args)
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
