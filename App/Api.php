<?php
namespace App;

use App\AppTrait;
use App\Authorize;
use App\CacheHandler;
use App\Constants;
use App\HttpRequest;
use App\HttpResponse;
use App\JsonEncode;
use App\Logs;
use App\Servers\Cache\Cache;
use App\Servers\Database\Database;
use App\Upload;
use App\Validation\Validator;

/**
 * Class to initialize api HTTP request
 *
 * This class process the api request
 *
 * @category   API
 * @package    Microservices
 * @author     Ramesh Narayan Jangid
 * @copyright  Ramesh Narayan Jangid
 * @version    Release: @1.0.0@
 * @since      Class available since Release 1.0.0
 */
class Api
{
    use AppTrait;

    /**
     * Cache Server connection object
     *
     * @var object
     */
    public $cache = null;

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
     * Authorize class object
     *
     * @var object
     */
    public $authorize = null;

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
        $this->authorize = new Authorize();
        $this->authorize->connectClientDB();
        $this->globalDB = getenv('globalDbName');
        $this->clientDB = getenv($this->authorize->clientDatabase);
        $this->cache = Cache::getObject();
        $this->db = Database::getObject();

        switch (HttpRequest::$REQUEST_METHOD) {
            case Constants::GET_METHOD:
                $this->processHttpGET();
                break;
            case Constants::POST_METHOD:
            case Constants::PUT_METHOD:
            case Constants::PATCH_METHOD:
            case Constants::DELETE_METHOD:
                $this->processHttpInsertUpdate();
                break;
        }
    }

    /**
     * Process HTTP GET request
     *
     * @return void
     */
    private function processHttpGET()
    {
        // Check & Process Upload
        $this->miscFunctionalityBeforeCollectingPayload();

        // Load Payloads
        HttpRequest::loadPayload();

        // Load Queries
        if (empty(HttpRequest::$__file__) || !file_exists(HttpRequest::$__file__)) {
            HttpResponse::return5xx(501, 'Path cannot be empty');
        }
        $config = include HttpRequest::$__file__;
        
        $this->jsonEncodeObj = new JsonEncode();
        $this->selectSubQuery($config);
        $this->jsonEncodeObj = null;
    }

    /**
     * Process update request
     *
     * @return void
     */
    private function processHttpInsertUpdate()
    {
        // Check & Process Upload
        $this->miscFunctionalityBeforeCollectingPayload();

        // Load Payloads
        HttpRequest::loadPayload();

        // Check & Process Cron / ThirdParty calls.
        $this->miscFunctionalityAfterCollectingPayload();

        // Load Config
        if (empty(HttpRequest::$__file__) || !file_exists(HttpRequest::$__file__)) {
            HttpResponse::return5xx(501, 'Path cannot be empty');
        }
        $config = include HttpRequest::$__file__;
        HttpRequest::$input['required'] = $this->getRequiredPayloadFields($config);

        // Perform action
        $response = [];
        foreach (HttpRequest::$input['payloadArr'] as &$payload) {
            $isValidData = true;
            if (HttpRequest::$REQUEST_METHOD === Constants::PATCH_METHOD) {
                if (count($payload) !== 1) {
                    HttpResponse::return4xx(404, 'Invalid payload: PATCH can update only one field');
                }
            }
            if (isset($payload['password'])) {
                $payload['password'] = password_hash($payload['password']);
            }
            HttpRequest::$input['payload'] = &$payload;

            // Configured Validation
            if (isset($config['validate']) || (count(HttpRequest::$input['required']) > 0)) {
                list($isValidData, $errors) = $this->validate(HttpRequest::$input, $config['validate']);
            }
            if ($isValidData!==true) {
                if (HttpRequest::$input['payloadType'] === 'Object') {
                    $response = ['data' => $payload, 'Error' => $errors];
                } else {
                    $response[] = ['data' => $payload, 'Error' => $errors];
                }
            } else {
                $this->db->begin();
                $res = $this->insertUpdateSubQuery($config);
                $this->db->commit();
                if (HttpRequest::$REQUEST_METHOD === Constants::POST_METHOD) {
                    $response[] = $res;
                }
            }
        }
        if (count($response) === 1) {
            $response = $response[0];
        }
        $this->jsonEncodeObj = new JsonEncode();
        if (count($response) > 0) {
            $this->jsonEncodeObj->encode($response);
        } else {
            $this->jsonEncodeObj->encode(['Status' => 200, 'Message' => 'Success']);
        }
        $this->jsonEncodeObj = null;
    }

    /**
     * Function to select sub queries recursively.
     *
     * @param array $subQuery Config from file
     * @return void
     */
    private function selectSubQuery($subQuery, $start = true)
    {
        $subQuery = ($start) ? [$subQuery] : $subQuery;
        foreach ($subQuery as $key => &$queryDetails) {
            if ($this->isAssoc($queryDetails)) {
                switch ($queryDetails['mode']) {
                    case 'singleRowFormat':
                        list($query, $params) = $this->getQueryAndParams($queryDetails);
                        $this->db->execDbQuery($query, array_values($params));
                        if ($row = $this->db->fetch()) {
                            ;
                        } else {
                            $row = [];
                        }
                        if (!isset($queryDetails['subQuery'])) {
                            if ($start) {
                                $this->jsonEncodeObj->startAssoc();
                            } else {
                                $this->jsonEncodeObj->startAssoc($key);
                            }
                            foreach($row as $key => $value) {
                                $this->jsonEncodeObj->addKeyValue($key, $value);
                            }
                            $this->jsonEncodeObj->endAssoc();
                        } else {
                            $resultColumns = array_keys($row);
                            foreach (array_keys($subQuery) as $col) {
                                if (in_array($col, $resultColumns)) {
                                    HttpResponse::return5xx(501, 'Invalid configuration: Conflicting column names');
                                }
                            }
                            if ($start) {
                                $this->jsonEncodeObj->startAssoc();
                            } else {
                                $this->jsonEncodeObj->startAssoc($key);
                            }
                            $subQueryKeys = [];
                            if (isset($queryDetails['subQuery'])) {
                                $subQueryKeys = array_keys($queryDetails['subQuery']);
                            }
                            foreach($row as $key => $value) {
                                if (in_array($key, $subQueryKeys)) {
                                    continue;
                                }
                                $this->jsonEncodeObj->addKeyValue($key, $value);
                            }
                        }
                        $this->db->closeCursor();
                        break;
                    case 'multipleRowFormat':
                        if (isset($queryDetails['subQuery'])) {
                            HttpResponse::return5xx(501, 'Invalid Configuration: multipleRowFormat can\'t have sub query');
                        }
                        if ($start) {
                            if (isset($queryDetails['countQuery'])) {
                                $queryDetailsCount = $queryDetails;
                                $queryDetailsCount['query'] = $queryDetailsCount['countQuery'];
                                unset($queryDetailsCount['countQuery']);
                                HttpRequest::$input['payload']['page']  = $_GET['page'] ?? 1;
                                HttpRequest::$input['payload']['perpage']  = $_GET['perpage'] ?? 10;
                                if (HttpRequest::$input['payload']['perpage'] > getenv('maxPerpage')) {
                                    HttpResponse::return4xx(403, 'perpage exceeds max perpage value of '.getenv('maxPerpage'));
                                }
                                HttpRequest::$input['payload']['start']  = (HttpRequest::$input['payload']['page'] - 1) * HttpRequest::$input['payload']['perpage'];
                                list($query, $params) = $this->getQueryAndParams($queryDetailsCount);
                                $this->db->execDbQuery($query, array_values($params));
                                $row = $this->db->fetch();
                                $this->db->closeCursor();
                                $totalRowsCount = $row['count'];
                                $totalPages = ceil($totalRowsCount/HttpRequest::$input['payload']['perpage']);
                                $this->jsonEncodeObj->startAssoc();
                                $this->jsonEncodeObj->addKeyValue('page', HttpRequest::$input['payload']['page']);
                                $this->jsonEncodeObj->addKeyValue('perpage', HttpRequest::$input['payload']['perpage']);
                                $this->jsonEncodeObj->addKeyValue('totalPages', $totalPages);
                                $this->jsonEncodeObj->addKeyValue('totalRecords', $totalRowsCount);
                                $this->jsonEncodeObj->startArray('data');
                            } else {
                                $this->jsonEncodeObj->startArray();
                            }
                        } else {
                            $this->jsonEncodeObj->startArray($key);
                        }
                        list($query, $params) = $this->getQueryAndParams($queryDetails);
                        if ($start) {
                            if (isset($queryDetails['countQuery'])) {
                                $start = HttpRequest::$input['payload']['start'];
                                $offset = HttpRequest::$input['payload']['perpage'];
                                $query .= " LIMIT {$start}, {$offset}";
                            }
                        }
                        $this->db->execDbQuery($query, array_values($params));
                        $singleColumn = false;
                        for ($i=0;$row=$this->db->fetch();) {
                            if ($i===0) {
                                if(count($row) === 1) {
                                    $singleColumn = true;
                                }
                                $i++;
                            }
                            if ($singleColumn) {
                                $this->jsonEncodeObj->encode($row[key($row)]);
                            } else {
                                $this->jsonEncodeObj->encode($row);
                            }
                        }
                        $this->jsonEncodeObj->endArray();
                        if ($start) {
                            if (isset($queryDetails['countQuery'])) {
                                $this->jsonEncodeObj->endAssoc();
                            }
                        }
                        $this->db->closeCursor();
                        break;
                }
                if (isset($queryDetails['subQuery'])) {
                    if (!$this->isAssoc($queryDetails['subQuery'])) {
                        HttpResponse::return5xx(501, 'Invalid Configuration: subQuery should be associative array');
                    }
                    $this->selectSubQuery($queryDetails['subQuery'], false);
                }
                if ($queryDetails['mode'] === 'singleRowFormat' && isset($queryDetails['subQuery'])) {
                    $this->jsonEncodeObj->endAssoc();
                }
            }
        }
    }

    /**
     * Function to insert/update sub queries recursively.
     *
     * @param array $subQuery Config from file
     * @return void
     */
    private function insertUpdateSubQuery($subQuery, $start = true)
    {
        $insertIds = [];
        $subQuery = ($start) ? [$subQuery] : $subQuery;
        foreach ($subQuery as &$queryDetails) {
            list($query, $params) = $this->getQueryAndParams($queryDetails);
            $this->db->execDbQuery($query, $params);
            if (isset($queryDetails['insertId'])) {
                $insertId = $this->db->lastInsertId();
                $insertIds = array_merge($insertIds, [$queryDetails['insertId'] => $insertId]);
                HttpRequest::$input['insertIdParams'][$queryDetails['insertId']] = $insertId;
            }
            $this->db->closeCursor();
            if (isset($queryDetails['subQuery'])) {
                $insertIds = array_merge($insertIds, $this->insertUpdateSubQuery($queryDetails['subQuery'], false));
            }
        }
        return $insertIds;
    }

    /**
     * Returns Query and Params for execution.
     *
     * @param array $subQuery Config from file
     * @param bool  $start    Flag to know start for recursive calls.
     * @return array
     */
    private function getRequiredPayloadFields($subQuery, $start = true)
    {
        $requiredPayloadFields = [];
        $subQuery = ($start) ? [$subQuery] : $subQuery;
        foreach ($subQuery as &$queryDetails) {
            if (isset($queryDetails['payload'])) {
                $queryPayload = &$queryDetails['payload'];
                foreach ($queryPayload as $var => $payload) {
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
            if (isset($queryDetails['where'])) {
                $queryWhere = &$queryDetails['where'];
                foreach ($queryWhere as $var => $payload) {
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
            if (isset($queryDetails['subQuery'])) {
                if (($rPayloadFields = $this->getRequired($queryDetails['subQuery'], false)) && count($rPayloadFields) > 0) {
                    foreach ($rPayloadFields as $r) {
                        if (!in_array($r, $requiredPayloadFields)) {
                            $requiredPayloadFields[] = $r;
                        }
                    }
                }
            }
        }
        return $requiredPayloadFields;
    }

    /**
     * Returns Query and Params for execution.
     *
     * @param array $queryDetails Config from file
     * @return array
     */
    private function getQueryAndParams(&$queryDetails)
    {
        $query = $queryDetails['query'];
        $stmtParams = [];
        $stmtWhereParams = [];
        if (isset($queryDetails['payload'])) {
            if (count($queryDetails['payload']) === 0) {
                HttpResponse::return5xx(501, 'Invalid config: Missing payload configuration');
            } else {
                if (strpos($query, '__SET__') !== false) {
                    $stmtParams = $this->getStmtParams($queryDetails['payload']);
                    $__SET__ = implode(', ',array_map(function ($v) { return '`' . implode('`.`',explode('.',str_replace('`','',$v))) . '` = ?';}, array_keys($stmtParams)));
                    $query = str_replace('__SET__', $__SET__, $query);
                } else {
                    HttpResponse::return5xx(501, 'Invalid query: Missing __SET__');
                }
            }
        }
        if (isset($queryDetails['where'])) {
            if (count($queryDetails['where']) === 0) {
                HttpResponse::return5xx(501, 'Invalid config: Missing where configuration');
            } else {
                if (strpos($query, '__WHERE__') !== false) {
                    $stmtWhereParams = $this->getStmtParams($queryDetails['where']);
                    $__WHERE__ = implode(' AND ',array_map(function ($v) { return '`' . implode('`.`',explode('.',str_replace('`','',$v))) . '` = ?';}, array_keys($stmtWhereParams)));
                    $query = str_replace('__WHERE__', $__WHERE__, $query);
                } else {
                    HttpResponse::return5xx(501, 'Invalid query: Missing __WHERE__');
                }
            }
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
     * @param array $queryPayload Config from file
     * @return array
     */
    private function getStmtParams(&$queryPayload)
    {
        $stmtParams = [];
        foreach ($queryPayload as $var => [$type, $typeKey]) {
            if ($type === 'custom') {
                $stmtParams[$var] = $typeKey;
            } else if ($type === 'payload' && !in_array($typeKey, HttpRequest::$input['required']) && !isset(HttpRequest::$input[$type][$typeKey])) {
                continue;
            } else {
                if (!isset(HttpRequest::$input[$type][$typeKey])) {
                    HttpResponse::return5xx(501, "Invalid configuration of '{$type}' for '{$typeKey}'");
                }
                $stmtParams[$var] = HttpRequest::$input[$type][$typeKey];
            }
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
        if (is_null($this->validator)) {
            $this->validator = new Validator();
        }
        return $this->validator->validate($data, $validationConfig);
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
    
    /**
     * Miscellaneous Functionality Before Collecting Payload
     *
     * @return void
     */
    function miscFunctionalityBeforeCollectingPayload()
    {
        switch (HttpRequest::$routeElements[1]) {
            case 'upload':
                Upload::init();
                die;
            case 'thirdParty':
                if (
                    isset(HttpRequest::$input['uriParams']['thirdParty']) &&
                    file_exists(__DOC_ROOT__ . '/ThirdParty/' . ucfirst(HttpRequest::$input['uriParams']['thirdParty']) . '.php')
                ) {
                    eval('ThirdParty\\' . ucfirst(HttpRequest::$input['uriParams']['thirdParty']) . '::init();');
                    die;
                } else {
                    HttpResponse::return4xx(404, "Invalid third party call");
                }
            case 'cache':
                CacheHandler::init();
                die;
        }
    }

    /**
     * Miscellaneous Functionality After Collecting Payload
     *
     * @return void
     */
    function miscFunctionalityAfterCollectingPayload()
    {

    }
}