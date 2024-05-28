<?php
namespace Validation;

use App\Constants;
use App\Env;
use App\HttpRequest;
use App\HttpResponse;
use App\Logs;
use App\Servers\Database\Database;
use App\Validation\ValidatorTrait;

/**
 * Validator
 *
 * This class is meant for global db related validation
 *
 * @category   Global DB Validator
 * @package    Microservices
 * @author     Ramesh Narayan Jangid
 * @copyright  Ramesh Narayan Jangid
 * @version    Release: @1.0.0@
 * @since      Class available since Release 1.0.0
 */
class GlobalValidator
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

    /**
     * Checks column value exist
     *
     * @param array $args Arguments
     * @return integer 0/1
     */
    private function checkColumnValueExist($args)
    {
        extract($args);
        $sql = "SELECT count(1) as `count` FROM `{$table}` WHERE `{$column}` = ? AND`{$primary}` = ?";
        $params = [$columnValue, $id];
        $this->db->execDbQuery($sql, $params);
        $row = $this->db->fetch();
        $this->db->closeCursor();
        return ($row['count'] === 0) ? false : true;
    }
}
