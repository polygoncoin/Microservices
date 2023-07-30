<?php
namespace App;

use App\Authorize;
use App\JsonEncode;
use App\Validation\Validator;
use App\PHPTrait;

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
    use PHPTrait;

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
        $this->authorize->init();
        $this->authorize->connectClientDB();
        $this->globalDB = $this->authorize->globalDB;
        $this->clientDB = getenv($this->authorize->clientDatabase);

        switch ($_SERVER['REQUEST_METHOD']) {
            case 'GET':
                $this->processHttpGET();
                break;
            case 'POST':
            case 'PUT':
            case 'PATCH':
            case 'DELETE':
                $this->processHttpInsertUpdate();
                break;
        }
    }

    /**
     * Return inputs
     *
     * @return array
     */
    private function getInputs()
    {
        // input details
        $input = [];

        // Load uriParams
        $input['uriParams'] = &$this->authorize->routeParams;

        // Load Read Only Session
        $input['readOnlySession'] = &$this->authorize->readOnlySession;

        return $input;
    }
    /**
     * Process HTTP GET request
     *
     * @return void
     */
    private function processHttpGET()
    {
        // Get Inputs
        $input = $this->getInputs();

        // Check & Process Upload
        $this->miscFunctionalityBeforeCollectingPayload($input);

        // Load $_GET as payload
        $input['payload'] = &$_GET;

        // Load Queries
        if (empty($this->authorize->__file__) || !file_exists($this->authorize->__file__)) {
            HttpErrorResponse::return5xx(501, 'Path cannot be empty');
        }
        $config = include $this->authorize->__file__;
        
        $this->jsonEncodeObj = new JsonEncode();
        $this->selectSubQuery($input, $config);
        $this->jsonEncodeObj = null;
    }

    /**
     * Process update request
     *
     * @return void
     */
    private function processHttpInsertUpdate()
    {
        // Get Inputs
        $input = $this->getInputs();

        // Check & Process Upload
        $this->miscFunctionalityBeforeCollectingPayload($input);

        // Load Payload
        parse_str(file_get_contents('php://input'), $payloadArr);
        if (!isset($payloadArr['data'])) {
            HttpErrorResponse::return4xx(404, 'Invalid data payload');
        }
        $payloadArr = json_decode($payloadArr['data'], true);
        $isAssoc = $this->isAssoc($payloadArr);
        if ($isAssoc) {
            $payloadArr = [$payloadArr];
        }

        // Check & Process Cron / ThirdParty calls.
        $this->miscFunctionalityAfterCollectingPayload(array_merge($input, ['payloadArr' => $payloadArr]));

        // Load Config
        if (empty($this->authorize->__file__) || !file_exists($this->authorize->__file__)) {
            HttpErrorResponse::return5xx(501, 'Path cannot be empty');
        }
        $config = include $this->authorize->__file__;
        $input['required'] = $this->getRequiredPayloadFields($config);

        // Perform action
        $response = [];
        foreach ($payloadArr as &$payload) {
            $isValidData = true;
            if ($this->authorize->requestMethod === 'PATCH') {
                if (count($payload) !== 1) {
                    HttpErrorResponse::return4xx(404, 'Invalid payload: PATCH can update only one field');
                }
            }
            if (isset($payload['password'])) {
                $payload['password'] = password_hash($payload['password']);
            }
            $input['payload'] = &$payload;

            // Configured Validation
            if (isset($config['validate']) || (count($input['required']) > 0)) {
                list($isValidData, $errors) = $this->validate($input, $config['validate']);
            }
            if ($isValidData!==true) {
                if ($isAssoc) {
                    $response = ['data' => $payload, 'Error' => $errors];
                } else {
                    $response[] = ['data' => $payload, 'Error' => $errors];
                }
            } else {
                $res = $this->insertUpdateSubQuery($input, $config);
                if ('POST' === $_SERVER['REQUEST_METHOD']) {
                    $response[] = $res;
                }
            }
        }
        $this->jsonEncodeObj = new JsonEncode();
        if ($this->authorize->requestMethod === 'POST') {
            if (count($response) === 1) {
                $response = $response[0];
            }
            $this->jsonEncodeObj->encode($response);
        } else if (count($response) > 0) {
            $this->jsonEncodeObj->encode($response);
        } else {
            $this->jsonEncodeObj->encode(['Status' => 200, 'Message' => 'Success']);
        }
        $this->jsonEncodeObj = null;
    }

    /**
     * Function to select sub queries recursively.
     *
     * @param array $input    Inputs
     * @param array $subQuery Config from file
     * @return void
     */
    private function selectSubQuery(&$input, $subQuery, $start = true)
    {
        $subQuery = ($start) ? [$subQuery] : $subQuery;
        foreach ($subQuery as $key => &$queryDetails) {
            if ($this->isAssoc($queryDetails)) {
                switch ($queryDetails['mode']) {
                    case 'singleRowFormat':
                        list($query, $params) = $this->getQueryAndParams($input, $queryDetails);
                        $this->authorize->db->execDbQuery($query, array_values($params));
                        if (!isset($queryDetails['subQuery'])) {
                            $this->jsonEncodeObj->encode($this->authorize->db->fetch());
                        } else {
                            if ($row = $this->authorize->db->fetch()) {
                                ;
                            } else {
                                $row = [];
                            }
                            $resultColumns = array_keys($row);
                            foreach (array_keys($subQuery) as $col) {
                                if (in_array($col, $resultColumns)) {
                                    HttpErrorResponse::return5xx(501, 'Invalid configuration: Conflicting column names');
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
                        $this->authorize->db->closeCursor();
                        break;
                    case 'multipleRowFormat':
                        if (isset($queryDetails['subQuery'])) {
                            HttpErrorResponse::return5xx(501, 'Invalid Configuration: multipleRowFormat can\'t have sub query');
                        }
                        if ($start) {
                            if (isset($queryDetails['countQuery'])) {
                                $queryDetailsCount = $queryDetails;
                                $queryDetailsCount['query'] = $queryDetailsCount['countQuery'];
                                unset($queryDetailsCount['countQuery']);
                                $input['payload']['page']  = $_GET['page'] ?? 1;
                                $input['payload']['perpage']  = $_GET['perpage'] ?? 10;
                                $input['payload']['start']  = ($input['payload']['page'] - 1) * $input['payload']['perpage'];
                                list($query, $params) = $this->getQueryAndParams($input, $queryDetailsCount);
                                $this->authorize->db->execDbQuery($query, array_values($params));
                                $row = $this->authorize->db->fetch();
                                $this->authorize->db->closeCursor();
                                $totalRowsCount = $row['count'];
                                $totalPages = ceil($totalRowsCount/$input['payload']['perpage']);
                                $this->jsonEncodeObj->startAssoc();
                                $this->jsonEncodeObj->addKeyValue('page', $input['payload']['page']);
                                $this->jsonEncodeObj->addKeyValue('perpage', $input['payload']['perpage']);
                                $this->jsonEncodeObj->addKeyValue('totalPages', $totalPages);
                                $this->jsonEncodeObj->addKeyValue('totalRecords', $totalRowsCount);
                                $this->jsonEncodeObj->startArray('data');
                            } else {
                                $this->jsonEncodeObj->startArray();
                            }
                        } else {
                            $this->jsonEncodeObj->startArray($key);
                        }
                        list($query, $params) = $this->getQueryAndParams($input, $queryDetails);
                        if ($start) {
                            if (isset($queryDetails['countQuery'])) {
                                $query .= " LIMIT {$input['payload']['start']}, {$input['payload']['perpage']}";
                            }
                        }
                        $this->authorize->db->execDbQuery($query, array_values($params));
                        $singleColumn = false;
                        for ($i=0;$row=$this->authorize->db->fetch();) {
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
                        $this->authorize->db->closeCursor();
                        break;
                }
                if (isset($queryDetails['subQuery'])) {
                    if (!$this->isAssoc($queryDetails['subQuery'])) {
                        HttpErrorResponse::return5xx(501, 'Invalid Configuration: subQuery should be associative array');
                    }
                    $this->selectSubQuery($input, $queryDetails['subQuery'], false);
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
     * @param array $input    Inputs
     * @param array $subQuery Config from file
     * @return void
     */
    private function insertUpdateSubQuery(&$input, $subQuery, $start = true)
    {
        $insertIds = [];
        $subQuery = ($start) ? [$subQuery] : $subQuery;
        foreach ($subQuery as &$queryDetails) {
            list($query, $params) = $this->getQueryAndParams($input, $queryDetails);
            $this->authorize->db->execDbQuery($query, $params);
            if (isset($queryDetails['insertId'])) {
                $insertId = $this->authorize->db->lastInsertId();
                $insertIds = array_merge($insertIds, [$queryDetails['insertId'] => $insertId]);
                $input['insertIdParams'][$queryDetails['insertId']] = $insertId;
            }
            $this->authorize->db->closeCursor();
            if (isset($queryDetails['subQuery'])) {
                $insertIds = array_merge($insertIds, $this->insertUpdateSubQuery($input, $queryDetails['subQuery'], false));
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
            $__SET__ = implode(', ',array_map(function ($v) { return '`' . implode('`.`',explode('.',str_replace('`','',$v))) . '` = ?';}, array_keys($stmtParams)));
            $query = str_replace('__SET__', $__SET__, $query);
        }
        if (isset($queryDetails['where'])) {
            $stmtWhereParams = $this->getStmtParams($input, $queryDetails['where']);
            $__WHERE__ = implode(' AND ',array_map(function ($v) { return '`' . implode('`.`',explode('.',str_replace('`','',$v))) . '` = ?';}, array_keys($stmtWhereParams)));
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
                    HttpErrorResponse::return5xx(501, "Invalid configuration of '{$type}' for '{$typeKey}'");
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
     * @param array $input without Payload
     * @return void
     */
    function miscFunctionalityBeforeCollectingPayload($input)
    {
        switch ($this->authorize->routeElements[0]) {
            case 'upload':
                eval('App\\Upload::init($input);');
                die;
            case 'thirdParty':
                if (
                    isset($input['uriParams']['party']) &&
                    file_exists(__DOC_ROOT__ . '/ThirdParty/' . ucfirst($input['uriParams']['party']) . '.php')
                ) {
                    eval('ThirdParty\\' . ucfirst($input['uriParams']['party']) . '::init($input);');
                    die;
                } else {
                    HttpErrorResponse::return4xx(404, "Invalid third party call");
                }
        }
    }

    /**
     * Miscellaneous Functionality After Collecting Payload
     *
     * @param array $input with Payload
     * @return void
     */
    function miscFunctionalityAfterCollectingPayload($input)
    {

    }
}