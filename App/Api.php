<?php
namespace App;

use App\Authorize;
use App\JsonEncode;
use App\Servers\Database;
use App\Validation\Validator;

/**
 * Class to initialize api HTTP request
 *
 * This class process the api request
 *
 * @category   Cache
 * @package    Microservices
 * @author     Ramesh Narayan Jangid
 * @copyright  Ramesh Narayan Jangid
 * @version    Release: @1.0.0@
 * @since      Class available since Release 1.0.0
 */
class Api
{
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
    public $globalDB = 'global';

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
    public $authorizeObj = null;

    /**
     * Validator class object
     *
     * @var object
     */
    public $validatorObj = null;

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
        $this->authorizeObj = new Authorize();
        $this->authorizeObj->init();
        $this->clientDB = getenv($this->authorizeObj->clientDatabase);

        $this->db = new Database(
            $this->authorizeObj->clientHostname,
            $this->authorizeObj->clientUsername,
            $this->authorizeObj->clientPassword,
            $this->authorizeObj->clientDatabase
        );
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
     * Process HTTP GET request
     *
     * @return void
     */
    private function processHttpGET()
    {
        // input details
        $input = [];

        // Load uriParams
        $input['uriParams'] = &$this->authorizeObj->routeParams;

        // Load Read Only Session
        $input['readOnlySession'] = &$this->authorizeObj->readOnlySession;

        // Load $_GET as payload
        $input['payload'] = &$_GET;

        // Load Queries
        $config = include $this->authorizeObj->__file__;
        
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
        // input details
        $input = [];

        // Load uriParams
        $input['uriParams'] = $this->authorizeObj->routeParams;

        // Load Read Only Session
        $input['readOnlySession'] = $this->authorizeObj->readOnlySession;

        // Load Payload
        parse_str(file_get_contents('php://input'), $payloadArr);
        $payloadArr = json_decode($payloadArr['data'], true);
        $isAssoc = $this->isAssoc($payloadArr);
        if ($isAssoc) {
            $payloadArr = [$payloadArr];
        }

        // Load Config
        $config = include $this->authorizeObj->__file__;

        $response = [];
        // Perform action
        foreach ($payloadArr as &$payload) {
            $isValidData = true;
            if ($this->authorizeObj->requestMethod === 'PATCH') {
                if (count($payload) !== 1) {
                    HttpErrorResponse::return404('Invalid payload: PATCH can update only one field');
                }
            }
            if (isset($payload['password'])) {
                $payload['password'] = password_hash($payload['password']);
            }
            $input['payload'] = &$payload;
            // Required validations.
            if (isset($config['validate'])) {
                $isValidData = $this->validate($input['payload'], $config['validate']);
            }
            if ($isValidData!==true) {
                $response[] = ['data' => $payload, 'Error' => $isValidData];
            }
            $res = $this->insertUpdateSubQuery($input, $config);
            if (!isset($payload['id'])) {
                $response[] = $res;
            }
        }
        $this->jsonEncodeObj = new JsonEncode();
        if ($this->authorizeObj->requestMethod === 'POST') {
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
                list($query, $params) = $this->getQueryAndParams($input, $queryDetails);
                try {
                    $stmt = $this->db->getStatement($query);
                    $stmt->execute(array_values($params));
                } catch(\PDOException $e) {
                    HttpErrorResponse::return501('Database error: ' . $e->getMessage());
                }
                switch ($queryDetails['mode']) {
                    case 'singleRowFormat':
                        if (!isset($queryDetails['subQuery'])) {
                            $this->jsonEncodeObj->encode($stmt->fetch(\PDO::FETCH_ASSOC));
                        } else {
                            $row = $stmt->fetch(\PDO::FETCH_ASSOC);
                            $resultColumns = array_keys($row);
                            foreach (array_keys($subQuery) as $col) {
                                if (in_array($col, $resultColumns)) {
                                    HttpErrorResponse::return501('Invalid configuration: Conflicting column names');
                                }
                            }
                            if ($start) {
                                $this->jsonEncodeObj->startAssoc();
                            } else {
                                $this->jsonEncodeObj->startAssoc($key);
                            }
                            foreach($row as $key => $value) {
                                $this->jsonEncodeObj->addKeyValue($key, $value);
                            }
                        }
                        break;
                    case 'multipleRowFormat':
                        if (isset($queryDetails['subQuery'])) {
                            HttpErrorResponse::return501('Invalid Configuration: multipleRowFormat can\'t have sub query');
                        }
                        if ($start) {
                            $this->jsonEncodeObj->startArray();
                        } else {
                            $this->jsonEncodeObj->startArray($key);
                        }
                        for (;$row=$stmt->fetch(\PDO::FETCH_ASSOC);) {
                            $this->jsonEncodeObj->encode($row);
                        }
                        $this->jsonEncodeObj->endArray();
                        break;
                }
                $stmt->closeCursor();
                if (isset($queryDetails['subQuery'])) {
                    if (!$this->isAssoc($queryDetails['subQuery'])) {
                        HttpErrorResponse::return501('Invalid Configuration: subQuery should be associative array');
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
            try {
                $stmt = $this->db->getStatement($query);
                $stmt->execute($params);
            } catch(\PDOException $e) {
                HttpErrorResponse::return501('Database error: ' . $e->getMessage());
            }
            if (isset($queryDetails['insertId'])) {
                $insertIds[] = $insertId = $this->db->lastInsertId();
                $input['insertIdParams'][$queryDetails['insertId']] = $insertId;
            }
            $stmt->closeCursor();
            if (isset($queryDetails['subQuery'])) {
                $insertIds = array_merge($insertIds,$this->insertUpdateSubQuery($input, $queryDetails['subQuery'], false));
            }
        }
        return $insertIds;
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
