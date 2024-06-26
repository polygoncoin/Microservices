<?php
namespace Microservices\App;

use Microservices\App\Constants;
use Microservices\App\Env;
use Microservices\App\HttpRequest;
use Microservices\App\HttpResponse;
use Microservices\App\Logs;
use Microservices\App\Servers\Database\Database;
use Microservices\Validation\ClientValidator;
use Microservices\Validation\GlobalValidator;

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
        if (Env::$dbDatabase === Env::$defaultDbDatabase) {
            $this->v = new GlobalValidator();
        } else {
            $this->v = new ClientValidator();
        }
    }

    /**
     * Validate payload
     *
     * @param array $input            Inputs
     * @param array $validationConfig Validation configuration.
     * @return array
     */
    public function validate($input, $validationConfig)
    {
        if (isset(($input['required'])) && count($input['required']) > 0) {
            if ((list($isValidData, $errors) = $this->validateRequired($input)) && !$isValidData) {
                return [$isValidData, $errors];
            }
        }

        return $this->v->validate($input, $validationConfig);
    }

    /**
     * Validate required payload
     *
     * @param array $input Inputs
     * @return array
     */
    private function validateRequired($input)
    {
        $isValidData = true;
        $errors = [];
        // Required fields payload validation
        $payload = $input['payload'];
        $required = $input['required'];
        if (count($payload) >= count($required)) {
            foreach ($required as $column) {
                if (!isset($payload[$column])) {
                    $errors[] = 'Invalid payload: '.$column;
                    $isValidData = false;
                }
            }
        } else {
            $errors[] = 'Invalid payload';
            $isValidData = false;
        }

        return [$isValidData, $errors];
    }
}
