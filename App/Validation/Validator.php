<?php
namespace App\Validation;

use App\HttpErrorResponse;
use App\Servers\Database\Database;
use App\Validation\ClientValidator;
use App\Validation\GlobalValidator;

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
    /**
     * Validator object
     */
    private $v = null;

    public function __construct()
    {
        if (Database::$database === 'globalDbName') {
            $this->v = new GlobalValidator();
        } else {
            $this->v = new ClientValidator();
        }
    }

    /**
     * Validate payload
     *
     * @param array $data             Payload data
     * @param array $validationConfig Validation configuration.
     * @return array
     */
    public function validate($input, $validationConfig)
    {
        return $this->v->validate($input, $validationConfig);
    }
}
