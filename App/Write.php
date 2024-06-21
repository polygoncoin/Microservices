<?php
namespace Microservices\App;

use Microservices\App\AppTrait;
use Microservices\App\Constants;
use Microservices\App\Env;
use Microservices\App\HttpRequest;
use Microservices\App\HttpResponse;
use Microservices\App\JsonEncode;
use Microservices\App\Servers\Database\Database;
use Microservices\App\Validation\Validator;

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
    public $jsonEncode = null;

    /**
     * Initialize
     *
     * @return boolean
     */
    public function init()
    {
        Env::$globalDB = Env::$defaultDbDatabase;
        Env::$clientDB = Env::$dbDatabase;

        $this->db = Database::getObject();
        $this->jsonEncode = HttpResponse::getJsonObject();

        return HttpResponse::isSuccess();
    }

    /**
     * Process
     *
     * @return boolean
     */
    public function process()
    {
        $Constants = __NAMESPACE__ . '\\Constants';
        $Env = __NAMESPACE__ . '\\Env';
        $HttpRequest = __NAMESPACE__ . '\\HttpRequest';

        // Load Queries
        $writeSqlConfig = include HttpRequest::$__file__;

        // Use results in where clause of sub queries recursively.
        $useHierarchy = $this->getUseHierarchy($writeSqlConfig);

        if (
            Env::$allowConfigRequest &&
            Env::$isConfigRequest
        ) {
            $this->processWriteConfig($writeSqlConfig, $useHierarchy);
        } else {
            $this->processWrite($writeSqlConfig, $useHierarchy);
        }

        return HttpResponse::isSuccess();
    }

    /**
     * Process write function for configuration.
     *
     * @param array   $writeSqlConfig Config from file
     * @param boolean $useHierarchy   Use results in where clause of sub queries recursively.
     * @return boolean
     */
    private function processWriteConfig(&$writeSqlConfig, $useHierarchy)
    {
        $success = HttpResponse::isSuccess();
        if (!$success) {
            return $success;
        }

        $response = [];
        $response['Route'] = HttpRequest::$configuredUri;
        $response['Payload'] = $this->getConfigParams($writeSqlConfig, true, $useHierarchy);

        $this->jsonEncode->startObject('Config');
        $this->jsonEncode->addKeyValue('Route', $response['Route']);
        $this->jsonEncode->addKeyValue('Payload', $response['Payload']);
        $this->jsonEncode->endObject();

        return HttpResponse::isSuccess();
    }    

    /**
     * Process Function to insert/update.
     *
     * @param array   $writeSqlConfig Config from file
     * @param boolean $useHierarchy   Use results in where clause of sub queries recursively.
     * @return boolean
     */
    private function processWrite(&$writeSqlConfig, $useHierarchy)
    {
        $success = HttpResponse::isSuccess();
        if (!$success) {
            return $success;
        }

        // Set required fields.
        HttpRequest::$input['requiredArr'] = $this->getRequired($writeSqlConfig, true, $useHierarchy);

        if (HttpRequest::$input['payloadType'] === 'Object') {
            $this->jsonEncode->startObject('Results');
        } else {
            $this->jsonEncode->startArray('Results');
        }

        // Perform action
        $jsonDecode = JsonDecode::getObject();
        $i_count = HttpRequest::$input['payloadType'] === 'Object' ? 1 : $jsonDecode->getCount('Payload');

        for ($i=0; $i < $i_count; $i++) {
            if ($i === 0) {
                if (HttpRequest::$input['payloadType'] === 'Object') {
                    $payload = $jsonDecode->get('Payload');
                } else {
                    $payload = $jsonDecode->get('Payload:'.$i);
                }
            } else {
                $payload = $jsonDecode->get('Payload:'.$i);
            }
            HttpRequest::$input['payload'] = $payload;
            if (Constants::$REQUEST_METHOD === Constants::$PATCH) {
                if (count(HttpRequest::$input['payload']) !== 1) {
                    HttpResponse::$httpStatus = 400;
                    $this->jsonEncode->startObject();
                    $this->jsonEncode->addKeyValue('Payload', $payload);
                    $this->jsonEncode->addKeyValue('Error', 'Invalid payload: PATCH can update only single field');
                    $this->jsonEncode->endObject();
                    continue;
                }
            }
            $isValidData = true;
            if (isset($writeSqlConfig['validate'])) {
                list($isValidData, $errors) = $this->validate($writeSqlConfig['validate']);
                if ($isValidData !== true) {
                    HttpResponse::$httpStatus = 400;
                    $this->jsonEncode->startObject();
                    $this->jsonEncode->addKeyValue('Payload', $payload);
                    $this->jsonEncode->addKeyValue('Error', $errors);
                    $this->jsonEncode->endObject();
                    continue;
                }
            }
            $this->db->begin();
            $response = [];
            $this->writeDB($writeSqlConfig, $payload, $useHierarchy, $response, HttpRequest::$input['requiredArr']);
            if ($this->db->beganTransaction === true) {
                $response['Success'] = true;
                $this->db->commit();
            } else {
                $response['Error'] = true;
            }
            if (HttpRequest::$input['payloadType'] !== 'Object') {
                $this->jsonEncode->startObject();
            }
            $this->jsonEncode->addKeyValue('Payload', $payload);
            $this->jsonEncode->addKeyValue('Response', $response);
            if (HttpRequest::$input['payloadType'] !== 'Object') {
                $this->jsonEncode->endObject();
            }
        }

        if (HttpRequest::$input['payloadType'] === 'Object') {
            $this->jsonEncode->endObject();
        } else {
            $this->jsonEncode->endArray();
        }

        return HttpResponse::isSuccess();
    }    

    /**
     * Function to insert/update sub queries recursively.
     *
     * @param array   $writeSqlConfig Config from file
     * @param boolean $payload        Payload.
     * @param boolean $useHierarchy   Use results in where clause of sub queries recursively.
     * @param array   $response       Response by reference.
     * @param array   $required       Required fields.
     * @return boolean
     */
    private function writeDB(&$writeSqlConfig, &$payloads, $useHierarchy, &$response, &$required)
    {
        $success = HttpResponse::isSuccess();
        if (!$success) {
            return $success;
        }

        $isAssoc = $this->isAssoc($payloads);

        $counter = 0;
        foreach (($isAssoc ? [$payloads] : $payloads) as &$payload) {
            if (!$this->db->beganTransaction) {
                $response['Error'] = 'Transaction rolled back';
                return;
            }
            HttpRequest::$input['payload'] = $payload;
            HttpRequest::$input['required'] = $required['__required__'];
    
            // Get Sql and Params
            list($sql, $sqlParams, $errors) = $this->getSqlAndParams($writeSqlConfig);
            if (!empty($errors)) {
                $response['Error'] = $errors;
                $this->db->rollback();
                return;
            }

            // Execute Query
            $this->db->execDbQuery($sql, $sqlParams);
            if (!$this->db->beganTransaction) {
                $response['Error'] = 'Something went wrong';
                return;
            }
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
                            return;
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

        return HttpResponse::isSuccess();
    }
}
