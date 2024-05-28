<?php
namespace App;

use App\AppTrait;
use App\Constants;
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
        $this->clientDB = getenv(Database::$database);
        $this->db = Database::getObject();
        $this->jsonObj = HttpResponse::getJsonObject();

        // Load Queries
        $writeSqlConfig = include HttpRequest::$__file__;

        // Use results in where clause of sub queries recursively.
        $useHierarchy = $this->getUseHierarchy($writeSqlConfig);

        if (
            HttpRequest::$allowConfigRequest &&
            HttpRequest::$isConfigRequest
        ) {
            $this->processWriteConfig($writeSqlConfig, $useHierarchy);
        } else {
            $this->processWrite($writeSqlConfig, $useHierarchy);
        }
    }

    /**
     * Process write function for configuration.
     *
     * @param array $writeSqlConfig Config from file
     * @param bool  $useHierarchy   Use results in where clause of sub queries recursively.
     * @return void
     */
    private function processWriteConfig(&$writeSqlConfig, $useHierarchy)
    {
        $response = [];
        $response['Route'] = HttpRequest::$configuredUri;
        $response['Payload'] = $this->getConfigParams($writeSqlConfig, true, $useHierarchy);
        $this->jsonObj->startAssoc('Config');
        $this->jsonObj->addKeyValue('Route', $response['Route']);
        $this->jsonObj->addKeyValue('Payload', $response['Payload']);
        $this->jsonObj->endAssoc();
    }    

    /**
     * Process Function to insert/update.
     *
     * @param array $writeSqlConfig Config from file
     * @param bool  $useHierarchy   Use results in where clause of sub queries recursively.
     * @return void
     */
    private function processWrite(&$writeSqlConfig, $useHierarchy)
    {
        // Set required fields.
        HttpRequest::$input['requiredArr'] = $this->getRequired($writeSqlConfig, true, $useHierarchy);

        if (HttpRequest::$input['payloadArrType'] === 'Object') {
            $this->jsonObj->startAssoc('Results');
        } else {
            $this->jsonObj->startArray('Results');
        }

        // Perform action
        foreach (HttpRequest::$input['payloadArr'] as &$payload) {
            HttpRequest::$input['payload'] = $payload;
            if (Constants::$REQUEST_METHOD === Constants::$PATCH) {
                if (count(HttpRequest::$input['payloadArr_ith']) !== 1) {
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
            $response = [];
            $this->writeDB($writeSqlConfig, $payload, $useHierarchy, $response, HttpRequest::$input['requiredArr']);
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
     * @param array $response       Response by reference.
     * @param array $required       Required fields.
     * @return void
     */
    private function writeDB(&$writeSqlConfig, &$payloads, $useHierarchy, &$response, &$required)
    {
        $isAssoc = $this->isAssoc($payloads);
        $counter = 0;
        foreach (($isAssoc ? [$payloads] : $payloads) as &$payload) {
            HttpRequest::$input['payload'] = $payload;
            HttpRequest::$input['required'] = $required['__required__'];
            // Get Sql and Params
            list($sql, $sqlParams) = $this->getSqlAndParams($writeSqlConfig);
            $this->db->execDbQuery($sql, $sqlParams);
            if (!$isAssoc && !isset($response[$counter])) {
                $response[$counter] = [];
            }
            if (isset($writeSqlConfig['insertId'])) {
                $insertId = $this->db->lastInsertId();
                if ($isAssoc) {
                    $response[$writeSqlConfig['insertId']] = $insertId;
                } else {
                    $response[$counter][$writeSqlConfig['insertId']] = $insertId;
                }
                HttpRequest::$input['insertIdParams'][$writeSqlConfig['insertId']] = $insertId;
            } else {
                $affectedRows = $this->db->affectedRows();
                if ($isAssoc) {
                    $response['affectedRows'] = $affectedRows;
                } else {
                    $response[$counter]['affectedRows'] = $affectedRows;
                }
            }
            $this->db->closeCursor();
            // subQuery for payload.
            if (isset($writeSqlConfig['subQuery'])) {
                foreach ($writeSqlConfig['subQuery'] as $module => &$_writeSqlConfig) {
                    if ($useHierarchy) { // use parent data of a payload.
                        if (isset($payload[$module])) {
                            $_payload = &$payload[$module];
                            $_required = &$required[$module] ?? [];
                        } else {
                            HttpResponse::return4xx(404, "Invalid payload: Module '{$module}' not supported.");
                        }
                    } else {
                        $_payload = &$payload;
                    }
                    $_useHierarchy = $useHierarchy ?? $this->getUseHierarchy($_writeSqlConfig);
                    if ($isAssoc) {
                        $response[$module] = [];
                        $_response = &$response[$module];
                    } else {
                        $response[$counter][$module] = [];
                        $_response = &$response[$counter][$module];
                    }
                    $this->writeDB($_writeSqlConfig, $_payload, $_useHierarchy, $_response, $_required);
                }
            }
            if (!$isAssoc) {
                $counter++;
            }
        }
    }
}
