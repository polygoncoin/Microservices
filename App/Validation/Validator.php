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
    private $authorize = null;

    public function __construct(&$authorize)
    {
        $this->authorize = $authorize;
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

    /**
     * Validate string is alphanumeric
     *
     * @param string $v
     * @return boolean
     */
    private function isAlphanumeric(&$v)
    {
        return preg_match('/^[a-z0-9 .\-]+$/i', $v);
    }

    /**
     * Validate string is an email
     *
     * @param string $v email address
     * @return boolean
     */
    private function isEmail(&$v)
    {
        return filter_var($v, FILTER_VALIDATE_EMAIL);
    }
}
