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

        // Perform action
        $response = [];
        foreach (HttpRequest::$input['payloadArr'] as &HttpRequest::$input['ith_payloadArr']) {
            HttpRequest::$input['payload'] = HttpRequest::$input['ith_payloadArr'];
            if (HttpRequest::$REQUEST_METHOD === Constants::PATCH) {
                if (count(HttpRequest::$input['payload']) !== 1) {
                    return ['data' => HttpRequest::$input['payload'], 'Error' => 'Invalid payload: PATCH can update only single field'];
                }
            }
            $isValidData = true;
            if (isset($writeSqlConfig['validate'])) {
                list($isValidData, $errors) = $this->validate($writeSqlConfig['validate']);
                if ($isValidData !== true) {
                    return ['data' => HttpRequest::$input['payload'], 'Error' => $errors];
                }
            }
            $this->db->begin();
            $res = $this->writeDB($writeSqlConfig, HttpRequest::$input['ith_payloadArr'], $_useHierarchy);
            $this->db->commit();
            if (!empty($res)) {
                $response[] = $res;
            }
        }
        if (!empty($response)) {
            if (HttpRequest::$input['payloadArrType'] === 'Object') {
                $response = $response[0];
            }
            $this->jsonObj->encode($response);
        } else {
            $this->jsonObj->encode(['Status' => 200, 'Message' => 'Success']);
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
        foreach (($isAssoc ? [$payloads] : $payloads) as &HttpRequest::$input['payload']){
            // Get Sql and Params
            list($sql, $sqlParams) = $this->getSqlAndParams($writeSqlConfig);
            $this->db->execDbQuery($sql, $sqlParams);
            if (isset($writeSqlConfig['insertId'])) {
                $insertId = $this->db->lastInsertId();
                if ($isAssoc) {
                    $response = [$writeSqlConfig['insertId'] => $insertId];
                } else {
                    $response[] = [$writeSqlConfig['insertId'] => $insertId];
                }
                HttpRequest::$input['insertIdParams'][$writeSqlConfig['insertId']] = $insertId;
            }
            $this->db->closeCursor();
        }
        if (isset($writeSqlConfig['subQuery'])) {
            foreach ($writeSqlConfig['subQuery'] as $module => &$writeSqlDetails) {
                if ($useHierarchy) { // use parent data of a payload.
                    if (isset(HttpRequest::$input['payload'][$module])) {
                        $module_payloads = &HttpRequest::$input['payload'][$module];
                    } else {
                        HttpResponse::return5xx(404, 'Invalid Configuration: Data missing in Hierarchy format.');
                    }
                } else {
                    $module_payloads = &HttpRequest::$input['payload'];
                }
                $_useHierarchy = $useHierarchy ?? $this->getUseHierarchy($writeSqlDetails);
                $res = $this->writeDB($writeSqlDetails, $module_payloads, $_useHierarchy);
                if (!empty($res)) {
                    if ($isAssoc) {
                        $response[$module] = $res;
                    } else {
                        $response[] = [$module => $res];
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
