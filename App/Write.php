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
        HttpRequest::$input['requiredPayload'] = $this->getRequired($writeSqlConfig);

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
            if (HttpRequest::$input['payloadArrType'] === 'Object') {
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
    private function process(&$writeSqlConfig, $payload)
    {
        $isValidData = true;
        if (HttpRequest::$REQUEST_METHOD === Constants::PATCH) {
            if (count($payload) !== 1) {
                HttpResponse::return4xx(404, 'Invalid payload: PATCH can update only one field');
            }
        }

        $this->db->begin();
        $payload = [$payload];
        $response = $this->writeDB($writeSqlConfig, $payload);
        $this->db->commit();
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
    private function writeDB($writeSqlConfig, &$payload, $first = true, &$requiredPayload = null)
    {
        $insertIds = [];
        $isAssoc = $this->isAssoc($payload);
        $payload = $isAssoc ? [$payload] : $payload;
        for ($i = 0, $iCount = count($payload); $i < $iCount; $i++) {
            HttpRequest::$input['payload'] = $payload[$i];
            if ($first) {
                $requiredPayload = HttpRequest::$input['requiredPayload'];
            }
            foreach ($writeSqlConfig as &$writeSqlDetails) {
                // Validation.
                HttpRequest::$input['required'] = $requiredPayload['required'];
                if (isset($writeSqlDetails['validate'])) {
                    list($isValidData, $errors) = $this->validate($writeSqlDetails['validate']);
                    if ($isValidData !== true) {
                        $insertIds[] = ['data' => $payload, 'Error' => $errors];
                        continue;
                    }
                }
                // Get Sql and Params
                list($sql, $sqlParams) = $this->getSqlAndParams($writeSqlDetails, $payload[$i]);
                $this->db->execDbQuery($sql, $sqlParams);
                if (isset($writeSqlDetails['insertId'])) {
                    $insertId = $this->db->lastInsertId();
                    if ($first) {
                        $insertIds = array_merge($insertIds, [$writeSqlDetails['insertId'] => $insertId]);
                    } else {
                        $insertIds[] = [$writeSqlDetails['insertId'] => $insertId];
                    }
                    HttpRequest::$input['insertIdParams'][$writeSqlDetails['insertId']] = $insertId;
                }
                $this->db->closeCursor();
                if (isset($writeSqlDetails['subQuery'])) {
                    foreach ($writeSqlDetails['subQuery'] as $k => $v) {
                        if (isset($payload[$i][$k])) {
                            $res = $this->writeDB([$writeSqlDetails['subQuery'][$k]], $payload[$i][$k], false, $requiredPayload[$k]);
                            if (!empty($res)) {
                                $insertIds = array_merge($insertIds, [$k => $res]);
                            }
                        }
                    }
                }
            }
        }
        if ($isAssoc && isset($insertIds[0])) {
            $insertIds = $insertIds[0];
        }
        return $insertIds;
    }

    /**
     * Sets required payload.
     *
     * @param array $writeSqlConfig Config from file
     * @param bool  $first          true to represent the first call in recursion.
     * @return void
     */
    private function getRequired($writeSqlConfig, $first = true)
    {
        $requiredPayloadFields = [];
        $requiredPayloadFields['required'] = [];
        if (isset($writeSqlConfig['payload'])) {
            foreach ($writeSqlConfig['payload'] as $var => $payload) {
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
                    if (!in_array($typeKey, $requiredPayloadFields['required'])) {
                        $requiredPayloadFields['required'][] = $typeKey;
                    }
                }
            }
        }
        if (isset($writeSqlConfig['where'])) {
            foreach ($writeSqlConfig['where'] as $var => $payload) {
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
                    if (!in_array($typeKey, $requiredPayloadFields['required'])) {
                        $requiredPayloadFields['required'][] = $typeKey;
                    }
                }
            }
        }
        if (isset($writeSqlConfig['subQuery'])) {
            foreach ($writeSqlConfig['subQuery'] as $k => $v) {
                $requiredPayloadFields[$k] = $this->getRequired([$writeSqlConfig['subQuery'][$k]], false);
            }
        }
        return $requiredPayloadFields;
    }

    /**
     * Validate payload
     *
     * @param array $validationConfig Validation config from Config file.
     * @return array
     */
    private function validate(&$validationConfig)
    {
        if (is_null($this->validator)) {
            $this->validator = new Validator();
        }
        return $this->validator->validate(HttpRequest::$input, $validationConfig);
    }
}
