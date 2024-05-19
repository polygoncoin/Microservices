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
    public $jsonObj = null;

    /**
     * Initialize
     *
     * @return void
     */
    public function init()
    {
        $this->globalDB = getenv('defaultDbDatabase');
        $this->clientDB = getenv(HttpRequest::$clientDatabase);
        $this->db = Database::getObject();
        $this->jsonObj = HttpResponse::getJsonObject();

        // Load Queries
        $writeSqlConfig = include HttpRequest::$__file__;

        // Use results in where clause of sub queries recursively.
        $_useHierarchy = $this->getUseHierarchy($writeSqlConfig);

        // Set required fields.
        HttpRequest::$input['requiredPayload'] = $this->getRequired($writeSqlConfig, true, $_useHierarchy);

        if (HttpRequest::$input['payloadArrType'] === 'Object') {
            $this->jsonObj->startAssoc('Results');
        } else {
            $this->jsonObj->startArray('Results');
        }

        // Perform action
        foreach (HttpRequest::$input['payloadArr'] as &$payload) {
            HttpRequest::$input['payload'] = $payload;
            if (HttpRequest::$REQUEST_METHOD === Constants::PATCH) {
                if (count(HttpRequest::$input['payload']) !== 1) {
                    HttpResponse::$httpStatus = 400;
                    $this->jsonObj->startAssoc();
                    $this->jsonObj->addKeyValue('Payload', $payload);
                    $this->jsonObj->addKeyValue('Error', 'Invalid payload: PATCH can update only single field');
                    $this->jsonObj->endAssoc();
                    continue;
                }
            }
            $isValidData = true;
            if (isset($writeSqlConfig['validate'])) {
                list($isValidData, $errors) = $this->validate($writeSqlConfig['validate']);
                if ($isValidData !== true) {
                    HttpResponse::$httpStatus = 400;
                    $this->jsonObj->startAssoc();
                    $this->jsonObj->addKeyValue('Payload', $payload);
                    $this->jsonObj->addKeyValue('Error', $errors);
                    $this->jsonObj->endAssoc();
                    continue;
                }
            }
            $this->db->begin();
            $response = $this->writeDB($writeSqlConfig, $payload, $_useHierarchy);
            $this->db->commit();
            if (!empty($response)) {
                if (HttpRequest::$input['payloadArrType'] !== 'Object') {
                    $this->jsonObj->startAssoc();
                }
                $this->jsonObj->addKeyValue('Payload', $payload);
                $this->jsonObj->addKeyValue('Response', $response);
                if (HttpRequest::$input['payloadArrType'] !== 'Object') {
                    $this->jsonObj->endAssoc();
                }
            }
        }
        if (HttpRequest::$input['payloadArrType'] === 'Object') {
            $this->jsonObj->endAssoc();
        } else {
            $this->jsonObj->endArray();
        }
    }

    /**
     * Function to insert/update sub queries recursively.
     *
     * @param array $writeSqlConfig Config from file
     * @param bool  $payload        Payload.
     * @param bool  $useHierarchy   Use results in where clause of sub queries recursively.
     * @return void
     */
    private function writeDB(&$writeSqlConfig, &$payloads, $useHierarchy)
    {
        $response = [];
        $isAssoc = $this->isAssoc($payloads);
        foreach (($isAssoc ? [$payloads] : $payloads) as &$payload){
            HttpRequest::$input['payload'] = $payload;
            // Get Sql and Params
            list($sql, $sqlParams) = $this->getSqlAndParams($writeSqlConfig);
            $this->db->execDbQuery($sql, $sqlParams);
            if (isset($writeSqlConfig['insertId'])) {
                $insertId = $this->db->lastInsertId();
                if ($isAssoc) {
                    $response = [$writeSqlConfig['insertId'] => $insertId];
                } else {
                    if (!isset($response[$writeSqlConfig['insertId']])) {
                        $response[$writeSqlConfig['insertId']] = [];
                    }
                    $response[$writeSqlConfig['insertId']][] = $insertId;
                }
                HttpRequest::$input['insertIdParams'][$writeSqlConfig['insertId']] = $insertId;
            }
            $this->db->closeCursor();
            // subQuery for payload.
            if (isset($writeSqlConfig['subQuery'])) {
                foreach ($writeSqlConfig['subQuery'] as $module => &$writeSqlDetails) {
                    if ($useHierarchy) { // use parent data of a payload.
                        if (isset($payload[$module])) {
                            $module_payload = &$payload[$module];
                        } else {
                            HttpResponse::return4xx(404, "Invalid payload: Module '{$module}' not supported.");
                        }
                    } else {
                        $module_payload = &$payload;
                    }
                    $_useHierarchy = $useHierarchy ?? $this->getUseHierarchy($writeSqlDetails);
                    $res = $this->writeDB($writeSqlDetails, $module_payload, $_useHierarchy);
                    if (!empty($res)) {
                        if ($isAssoc) {
                            $response[$module] = $res;
                        } else {
                            $response[] = [$module => $res];
                        }
                    }
                }
            }
        }
        if ($isAssoc && isset($response[0])) {
            $response = $response[0];
        }
        return $response;
    }
}
