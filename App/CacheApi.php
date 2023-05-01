<?php
namespace App;

use App\Authorize;
use App\JsonEncode;
use App\Validation\Validator;

/**
 * Class initializing Cache Updater api's
 *
 * This class processes the cache
 *
 * @category   Cache Updater
 * @package    Microservices
 * @author     Ramesh Narayan Jangid
 * @copyright  Ramesh Narayan Jangid
 * @version    Release: @1.0.0@
 * @since      Class available since Release 1.0.0
 */
class CacheApi
{
    /**
     * Authorize class object
     *
     * @var object
     */
    public $authorizeObj = null;

    /**
     * Initialize
     *
     * @return void
     */
    public static function init()
    {
        (new self)->process();
    }

    /**
     * Process all functions
     *
     * @return void
     */
    public function process()
    {
        $this->authorizeObj = new Authorize();
        $this->authorizeObj->init();

        switch ($_SERVER['REQUEST_METHOD']) {
            case 'PUT':
                $this->processCacheUpdate();
                break;
        }
    }

    /**
     * Process update request
     *
     * @return void
     */
    private function processCacheUpdate()
    {
        // input details
        $input = [];

        // Load uriParams
        $input['uriParams'] = $this->authorizeObj->routeParams;

        // Load Read Only Session
        $input['readOnlySession'] = $this->authorizeObj->readOnlySession;

        // Load Payload
        parse_str(file_get_contents('php://input'), $payloadArr);
        $payload = json_decode($payloadArr['data'], true);
        if (!$this->isAssoc($payload)) {
            //error out
        }

        // Load Config
        $config = include $this->authorizeObj->__file__;

        $response = [];
        // Perform action
        $isValidData = true;
        $input['payload'] = &$payload;
        // Required validations.
        if (isset($config['validate'])) {
            $isValidData = $this->validate($input['payload'], $config['validate']);
        }
        if ($isValidData!==true) {
            $response[] = ['data' => $payload, 'Error' => $isValidData];
        }
        // $res = $this->insertUpdateSubQuery($input, $config);
        // if (!isset($payload['id'])) {
        //     $response[] = $res;
        // }
        $this->jsonEncodeObj = new JsonEncode();
        $this->jsonEncodeObj->encode(['Status' => 200, 'Message' => 'Success']);
        $this->jsonEncodeObj = null;
    }

    /**
     * Returns Query and Params for execution.
     *
     * @param array $input        Inputs
     * @param array $queryDetails Config from file
     * @return array
     */
    private function getQueryAndParams(&$input, &$queryDetails)
    {
        $query = $queryDetails['query'];
        $stmtParams = [];
        $stmtWhereParams = [];
        if (isset($queryDetails['payload'])) {
            $stmtParams = $this->getStmtParams($input, $queryDetails['payload']);
            $__SET__ = implode(', ',array_map(function ($v) { return '`' . str_replace('`','',$v) . '` = ?';}, array_keys($stmtParams)));
            $query = str_replace('__SET__', $__SET__, $query);
        }
        if (isset($queryDetails['where'])) {
            $stmtWhereParams = $this->getStmtParams($input, $queryDetails['where']);
            $__WHERE__ = implode(', ',array_map(function ($v) { return '`' . str_replace('`','',$v) . '` = ?';}, array_keys($stmtWhereParams)));
            $query = str_replace('__WHERE__', $__WHERE__, $query);
        }
        $params = [];
        foreach ($stmtParams as $v) {
            $params[] = $v;
        }
        foreach ($stmtWhereParams as $v) {
            $params[] = $v;
        }
        return [$query, $params];
    }

    /**
     * Generates Params for statement to execute.
     *
     * @param array $input        Inputs
     * @param array $queryPayload Config from file
     * @return array
     */
    private function getStmtParams(&$input, &$queryPayload)
    {
        $stmtParams = [];
        foreach ($queryPayload as $var => [$type, $typeKey]) {
            if ($type === 'custom') {
                $typeValue = $typeKey;
            } else {
                if (!isset($input[$type][$typeKey])) {
                    HttpErrorResponse::return501("Invalid configuration of '{$type}' for '{$typeKey}'");
                }
                $typeValue = $input[$type][$typeKey];
            }
            $stmtParams[$var] = $typeValue;
        }
        return $stmtParams;
    }

    /**
     * Validate payload
     *
     * @param array $data             Payload data
     * @param array $validationConfig Validation configuration.
     * @return array
     */
    private function validate(&$data, &$validationConfig)
    {
        if (is_null($this->validatorObj)) {
            $this->validatorObj = new Validator($this->db);
        }
        return $this->validatorObj->validate($data, $validationConfig);
    }

    /**
     * Function to find wether privider array is associative/simple array
     *
     * @param array $arr Array to search for associative/simple array
     * @return boolean
     */
    private function isAssoc($arr)
    {
        $assoc = false;
        $i = 0;
        foreach ($arr as $k => &$v) {
            if ($k !== $i++) {
                $assoc = true;
                break;
            }
        }
        return $assoc;
    }
}
