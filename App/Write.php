<?php
namespace App;

use App\AppTrait;
use App\HttpRequest;
use App\HttpResponse;
use App\JsonEncode;
use App\Servers\Database\Database;
use App\Validation\Validator;

/**
 * Class to initialize DB Write operation
 *
 * This class process the POST/PUT/PATCH/DELETE api request
 *
 * @category   CRUD Write
 * @package    Microservices
 * @author     Ramesh Narayan Jangid
 * @copyright  Ramesh Narayan Jangid
 * @version    Release: @1.0.0@
 * @since      Class available since Release 1.0.0
 */
class Write
{
    use AppTrait;

    /**
     * DB Server connection object
     *
     * @var object
     */
    public $db = null;

    /**
     * Global DB
     *
     * @var string
     */
    public $globalDB = null;

    /**
     * Client database
     *
     * @var string
     */
    public $clientDB = null;

    /**
     * Validator class object
     *
     * @var object
     */
    public $validator = null;

    /**
     * JsonEncode class object
     *
     * @var object
     */
    public $jsonEncodeObj = null;

    /**
     * Initialize
     *
     * @return void
     */
    public function init()
    {
        $this->globalDB = getenv('globalDbName');
        $this->clientDB = getenv(HttpRequest::$clientDatabase);
        $this->db = Database::getObject();
        $this->jsonEncodeObj = JsonEncode::getObject();

        // Load Queries
        $writeSqlConfig = include HttpRequest::$__file__;

        // Set required fields.
        $this->getRequired($writeSqlConfig);

        // Perform action
        $response = [];
        $writeSqlConfig = [$writeSqlConfig];
        foreach (HttpRequest::$input['payloadArr'] as &$payload) {
            $res = $this->process($writeSqlConfig, $payload);
            if (!empty($res)) {
                $response[] = $res;
            }
        }
        if (!empty($response)) {
            if (HttpRequest::$input['payloadType'] === 'Object') {
                $response = $response[0];
            }
            $this->jsonEncodeObj->encode($response);
        } else {
            $this->jsonEncodeObj->encode(['Status' => 200, 'Message' => 'Success']);
        }
    }

    /**
     * Process DB write operation
     *
     * @param array $writeSqlConfig Config from file
     * @param array $payload        Single Payload from array.
     * @return void
     */
    private function process(&$writeSqlConfig, &$payload)
    {
        $isValidData = true;
        if (HttpRequest::$REQUEST_METHOD === Constants::PATCH) {
            if (count($payload) !== 1) {
                HttpResponse::return4xx(404, 'Invalid payload: PATCH can update only one field');
            }
        }
        HttpRequest::$input['payload'] = $payload;

        // Configured Validation
        if (isset($writeSqlConfig['validate'])) {
            list($isValidData, $errors) = $this->validate($writeSqlConfig);
        }
        if ($isValidData !== true) {
            $response = ['data' => $payload, 'Error' => $errors];
        } else {
            $this->db->begin();
            $response = $this->writeDB($writeSqlConfig, $payload);
            $this->db->commit();
        }
        return $response;
    }

    /**
     * Function to insert/update sub queries recursively.
     *
     * @param array $writeSqlConfig Config from file
     * @param array $payload        Single Payload from array.
     * @param bool  $first          true to represent the first call in recursion.
     * @return void
     */
    private function writeDB(&$writeSqlConfig, &$payload, $first = true)
    {
        $insertIds = [];
        foreach ($writeSqlConfig as $configKey => &$writeSqlDetails) {
            // Get Sql and Params
            if ($first) {
                list($sql, $params) = $this->getSqlAndParams($writeSqlDetails, $payload);
            } else if (isset($payload[$configKey])) {
                list($sql, $params) = $this->getSqlAndParams($writeSqlDetails, $payload[$configKey]);
            } else {
                continue;
            }
            $multipleCount = 1;
            //Note can't have same param in hierarchy/subQuery for insert/update.
            $sParams = []; //single
            $mParams = [];//multiple
            if (!$first) {
                foreach($params as $param => $val) {
                    if (is_array($val)) {
                        $count = count($val);
                        $multipleCount = ($multipleCount === 1 && $multipleCount !== $count) ? $count : 1;
                        if ($multipleCount !== $count) {
                            HttpResponse::return5xx(401, 'Invalid JSON: Multiple payloads Mismatch.');
                        }
                        $mParams[] = $param;
                    } else {
                        $sParams[$param] = $val;
                    }
                }
            } else {
                $sParams = $params;
                $mParams = [];
            }
            $counter = 0;
            do {
                $sqlParams = $sParams;
                if (count($mParams) > 0) {
                    foreach ($mParams as $param) {
                        $sqlParams[$param] = $params[$param][$counter];
                    }
                }
                $this->db->execDbQuery($sql, $sqlParams);
                if (isset($writeSqlDetails['insertId'])) {
                    $insertId = $this->db->lastInsertId();
                    $insertIds = array_merge($insertIds, [$writeSqlDetails['insertId'] => $insertId]);
                    HttpRequest::$input['insertIdParams'][$writeSqlDetails['insertId']] = $insertId;
                }
                $this->db->closeCursor();
                if (isset($writeSqlDetails['subQuery'])) {
                    $insertIds = array_merge($insertIds, $this->writeDB($writeSqlDetails['subQuery']), false);
                }
                $counter++;
            } while (--$multipleCount > 0);
            if (!$first) {
                $insertIds = [$configKey => $insertIds];
            }
        }
        return $insertIds;
    }

    /**
     * Sets required payload.
     *
     * @param array $writeSqlConfig Config from file
     * @return void
     */
    private function getRequired(&$writeSqlConfig)
    {
        $requiredPayloadFields = [];
        foreach ($writeSqlConfig as &$writeSqlDetails) {
            if (isset($writeSqlDetails['payload'])) {
                foreach ($writeSqlDetails['payload'] as $var => $payload) {
                    $required = false;
                    $count = count($payload);
                    switch ($count) {
                        case 3:
                            list($type, $typeKey, $required) = $payload;
                            break;
                        case 2: 
                            list($type, $typeKey) = $payload;
                            break;
                    }
                    if ($required && $type === 'payload') {
                        if (!in_array($typeKey, $requiredPayloadFields)) {
                            $requiredPayloadFields[] = $typeKey;
                        }
                    }
                }
            }
            if (isset($writeSqlDetails['where'])) {
                foreach ($writeSqlDetails['where'] as $var => $payload) {
                    $required = false;
                    $count = count($payload);
                    switch ($count) {
                        case 3:
                            list($type, $typeKey, $required) = $payload;
                            break;
                        case 2: 
                            list($type, $typeKey) = $payload;
                            break;
                    }
                    if ($required && $type === 'payload') {
                        if (!in_array($typeKey, $requiredPayloadFields)) {
                            $requiredPayloadFields[] = $typeKey;
                        }
                    }
                }
            }
            if (isset($writeSqlDetails['subQuery'])) {
                if (($rPayloadFields = $this->getRequired($writeSqlDetails['subQuery'])) && count($rPayloadFields) > 0) {
                    foreach ($rPayloadFields as $r) {
                        if (!in_array($r, $requiredPayloadFields)) {
                            $requiredPayloadFields[] = $r;
                        }
                    }
                }
            }
        }
        HttpRequest::$input['required'] = $requiredPayloadFields;
    }

    /**
     * Validate payload
     *
     * @param array $writeSqlConfig Config from file
     * @return array
     */
    private function validate(&$writeSqlConfig)
    {
        if (is_null($this->validator)) {
            $this->validator = new Validator();
        }
        return $this->validator->validate(HttpRequest::$input, $writeSqlConfig['validate']);
    }
}
