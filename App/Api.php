<?php
namespace App;

use App\Authorize;
use App\JsonEncode;
use App\Servers\Database;
use App\Validation\Validate;

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
     * Validation class object
     *
     * @var object
     */
    public $validationObj = null;

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
                $this->processHttpPOST();
                break;
            case 'PUT':
                $this->processHttpPUT();
                break;
            case 'PATCH':
                $this->processHttpPATCH();
                break;
            case 'DELETE':
                $this->processHttpDELETE();
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
        $input['uriParams'] = $this->authorizeObj->routeParams;

        // Load Read Only Session
        $input['readOnlySession'] = $this->authorizeObj->readOnlySession;

        // Load Queries
        $config = include $this->authorizeObj->__file__;

        if ($this->isAssoc($config)) {
            $this->jsonEncodeObj = new JsonEncode();
            $query = $config['query'];
            $stmtWhereParams = [];
            if (isset($config['where'])) {
                $stmtWhereParams = $this->getStmtParams($config['where'], $input);
                $__WHERE__ = implode(', ',array_map(function ($v) { return '`' . str_replace('`','',$v) . '` = ?';}, array_keys($stmtWhereParams)));
                $query = str_replace('__WHERE__', $__WHERE__, $query);    
            }
            $stmt = $this->db->select($query);
            $stmt->execute(array_values($stmtWhereParams));
            switch ($config['mode']) {
                case 'singleRowFormat':
                    if (!isset($config['subQuery'])) {
                        $this->jsonEncodeObj->encode($stmt->fetch(\PDO::FETCH_ASSOC));
                    } else {
                        $resultColums = $stmt->fetchAll(\PDO::FETCH_COLUMN);
                        foreach (array_keys($subQueryCols) as $cols) {
                            if (in_array($cols, $resultColums)) {
                                HttpErrorResponse::return501('Invalid configuration: Conflicting column names');
                            }
                        }
                        $this->jsonEncodeObj->startAssoc();
                        foreach($stmt->fetch(\PDO::FETCH_ASSOC) as $key => $value) {
                            $jsonthis->jsonEncodeObjEncode->addKeyValue($key, $value);
                        }
                    }
                    break;
                case 'multipleRowFormat':
                    if (isset($config['subQuery'])) {
                        HttpErrorResponse::return501('Invalid Configuration: multipleRowFormat can\'t have sub query');
                    }
                    $this->jsonEncodeObj->startArray();
                    for (;$row=$stmt->fetch(\PDO::FETCH_ASSOC);) {
                        $this->jsonEncodeObj->encode($row);
                    }
                    $this->jsonEncodeObj->endArray();
                    break;
            }
            $stmt->closeCursor();
            if (isset($config['subQuery'])) {
                if (!$this->isAssoc($config['subQuery'])) {
                    HttpErrorResponse::return501('Invalid Configuration: subQuery should be associative array');
                }
                $this->selectSubQuery($input, $config['subQuery']);
            }
            if ($config['mode'] === 'singleRowFormat' && isset($config['subQuery'])) {
                $this->jsonEncodeObj->endAssoc();
            }
            $this->jsonEncodeObj = null;
        }
    }

    /**
     * Function to select sub queries recursively.
     *
     * @param array $input    Inputs
     * @param array $subQuery Config from file
     * @return void
     */
    private function selectSubQuery($input, $subQuery)
    {
        foreach ($subQuery as $key => $config) {
            if (isAssoc($config)) {
                $query = $config['query'];
                $stmtWhereParams = [];
                if (isset($config['where'])) {
                    $stmtWhereParams = $this->getStmtParams($config['where'], $input);
                    $__WHERE__ = implode(', ',array_map(function ($v) { return '`' . str_replace('`','',$v) . '` = ?';}, array_keys($stmtWhereParams)));
                    $query = str_replace('__WHERE__', $__WHERE__, $query);    
                }
                $stmt = $this->db->select($query);
                $stmt->execute(array_values($stmtWhereParams));
                switch ($config['mode']) {
                    case 'singleRowFormat':
                        if (!isset($config['subQuery'])) {
                            $this->jsonEncodeObj->encode($stmt->fetch(\PDO::FETCH_ASSOC));
                        } else {
                            $resultColums = $stmt->fetchAll(\PDO::FETCH_COLUMN);
                            foreach (array_keys($subQueryCols) as $cols) {
                                if (in_array($cols, $resultColums)) {
                                    HttpErrorResponse::return501('Invalid configuration: Conflicting column names');
                                }
                            }
                            $this->jsonEncodeObj->startAssoc($key);
                            foreach($stmt->fetch(\PDO::FETCH_ASSOC) as $key => $value) {
                                $jsonthis->jsonEncodeObjEncode->addKeyValue($key, $value);
                            }
                        }
                        break;
                    case 'multipleRowFormat':
                        if (isset($config['subQuery'])) {
                            HttpErrorResponse::return501('Invalid Configuration: multipleRowFormat can\'t have sub query');
                        }
                        $this->jsonEncodeObj->startArray($key);
                        for (;$row=$stmt->fetch(\PDO::FETCH_ASSOC);) {
                            $this->jsonEncodeObj->encode($row);
                        }
                        $this->jsonEncodeObj->endArray();
                        break;
                }
                $stmt->closeCursor();
                if (isset($config['subQuery'])) {
                    if (!$this->isAssoc($config['subQuery'])) {
                        HttpErrorResponse::return501('Invalid Configuration: subQuery should be associative array');
                    }
                    $this->selectSubQuery($input, $config['subQuery']);
                }
                if ($config['mode'] === 'singleRowFormat' && isset($config['subQuery'])) {
                    $this->jsonEncodeObj->endAssoc();
                }
            }
        }
    }

    /**
     * Process HTTP POST request
     *
     * @return void
     */
    private function processHttpPOST()
    {
        // input details
        $input = [];

        // Load uriParams
        $input['uriParams'] = $this->authorizeObj->routeParams;

        // Load Read Only Session
        $input['readOnlySession'] = $this->authorizeObj->readOnlySession;

        // Load Payload
        parse_str(file_get_contents('php://input'), $payloadArr);
        $payloadArr = json_decode($payloadArr['data'], true)['data'];
        $isAssoc = $this->isAssoc($payloadArr);
        if ($isAssoc) {
            $payloadArr = [$payloadArr];
        }
        $dataCount = count($payloadArr);

        // Load Config
        $config = include $this->authorizeObj->__file__;

        // Perform action
        for ($i = 0; $i < $dataCount; $i++) {
            $isValidData = true;
            $input['payload'] = &$payloadArr[$i];
            // Required validations.
            if (isset($config['validate'])) {
                $isValidData = $this->validate($dataCount, $payloadArr, $config['validate']);
            }
            if ($isValidData) {
                foreach ($config['queries'] as $key => &$queryDetails) {
                    $stmtParams = [];
                    $stmtParams = $this->getStmtParams($queryDetails['payload'], $input);
                    $__SET__ = implode(', ',array_map(function ($v) { return '`' . str_replace('`','',$v) . '` = ?';}, array_keys($stmtParams)));
                    $query = str_replace('__SET__', 'SET ' . $__SET__, $queryDetails['query']);
                    $stmt = $this->db->insert($query);
                    $stmt->execute(array_values($stmtParams));
                    $insertId = $this->db->lastInsertId();
                    if (isset($config['queries']['insertId'])) {
                        $input['insertIdParams'][$config['queries']['insertId']] = $insertId;
                    }
                    $stmt->closeCursor();
                    if (isset($config['queries'][$key]['subQuery'])) {
                        $this->insertSubQuery($input, $config['queries'][$key]['subQuery']);
                    }
                }
            }
        }
    }

    /**
     * Function to insert sub queries recursively.
     *
     * @param array $input    Inputs
     * @param array $subQuery Config from file
     * @return void
     */
    private function insertSubQuery(&$input, &$subQuery)
    {
        foreach ($subQuery as $key => &$queryDetails) {
            $stmtParams = $this->getStmtParams($queryDetails['payload'], $input);
            $__SET__ = implode(', ',array_map(function ($v) { return '`' . str_replace('`','',$v) . '` = ?';}, array_keys($stmtParams)));
            $query = str_replace('__SET__', $__SET__, $queryDetails['query']);
            $stmt = $this->db->insert($query);
            $stmt->execute(array_values($stmtParams));
            $insertId = $this->db->lastInsertId();
            if (isset($queryDetails['insertId'])) {
                $input['insertIdParams'][$queryDetails['insertId']] = $insertId;
            }
            $stmt->closeCursor();
            if (isset($queryDetails['subQuery'])) {
                $this->insertSubQuery($input, $queryDetails['subQuery']);
            }
        }
    }

    /**
     * Process HTTP PUT request
     *
     * @return void
     */
    private function processHttpPUT()
    {
        $this->processHttpUpdate();
    }

    /**
     * Process HTTP PATCH request
     *
     * @return void
     */
    private function processHttpPATCH()
    {
        $this->processHttpUpdate();
    }

    /**
     * Process HTTP DELETE request
     *
     * @return void
     */
    private function processHttpDELETE()
    {
        $this->processHttpUpdate();
    }
    
    private function processHttpUpdate()
    {
        // input details
        $input = [];

        // Load uriParams
        $input['uriParams'] = $this->authorizeObj->routeParams;

        // Load Read Only Session
        $input['readOnlySession'] = $this->authorizeObj->readOnlySession;

        // Load Payload
        parse_str(file_get_contents('php://input'), $payloadArr);
        $payloadArr = json_decode($payloadArr['data'], true)['data'];
        $isAssoc = $this->isAssoc($payloadArr);
        if ($isAssoc) {
            $payloadArr = [$payloadArr];
        }
        $dataCount = count($payloadArr);

        // Load Config
        $config = include $this->authorizeObj->__file__;

        // Perform action
        for ($i = 0; $i < $dataCount; $i++) {
            $isValidData = true;
            $input['payload'] = &$payloadArr[$i];
            // Required validations.
            if (isset($config['validate'])) {
                $isValidData = $this->validate($dataCount, $payloadArr, $config['validate']);
            }
            if ($isValidData) {
                foreach ($config['queries'] as $key => &$queryDetails) {
                    $stmtParams = $this->getStmtParams($queryDetails['payload'], $input);
                    $__SET__ = implode(', ',array_map(function ($v) { return '`' . str_replace('`','',$v) . '` = ?';}, array_keys($stmtParams)));
                    $stmtWhereParams = $this->getStmtParams($queryDetails['where'], $input);
                    $__WHERE__ = implode(', ',array_map(function ($v) { return '`' . str_replace('`','',$v) . '` = ?';}, array_keys($stmtWhereParams)));
                    $query = $queryDetails['query'];
                    $query = str_replace('__SET__', $__SET__, $query);
                    $query = str_replace('__WHERE__', $__WHERE__, $query);
                    $stmt = $this->db->update($query);
                    $params = [];
                    foreach ($stmtParams as $v) {
                        $params[] = $v;
                    }
                    foreach ($stmtWhereParams as $v) {
                        $params[] = $v;
                    }
                    $stmt->execute($params);
                    $stmt->closeCursor();
                    if (isset($config['queries'][$key]['subQuery'])) {
                        $this->updateSubQuery($input, $config['queries'][$key]['subQuery']);
                    }
                }
            }
        }
    }

    /**
     * Function to update sub queries recursively.
     *
     * @param array $input    Inputs
     * @param array $subQuery Config from file
     * @return void
     */
    private function updateSubQuery(&$input, &$subQuery)
    {
        foreach ($subQuery as $key => &$queryDetails) {
            $stmtParams = $this->getStmtParams($queryDetails['payload'], $input);
            $__SET__ = implode(', ',array_map(function ($v) { return '`' . str_replace('`','',$v) . '` = ?';}, array_keys($stmtParams)));
            $stmtWhereParams = $this->getStmtParams($queryDetails['where'], $input);
            $__WHERE__ = implode(', ',array_map(function ($v) { return '`' . str_replace('`','',$v) . '` = ?';}, array_keys($stmtWhereParams)));
            $query = $queryDetails['query'];
            $query = str_replace('__SET__', $__SET__, $query);
            $query = str_replace('__WHERE__', $__WHERE__, $query);
            $stmt = $this->db->update($query);
            $params = [];
            foreach ($stmtParams as $v) {
                $params[] = $v;
            }
            foreach ($stmtWhereParams as $v) {
                $params[] = $v;
            }
            $stmt->execute($params);
            $stmt->closeCursor();
            if (isset($queryDetails['subQuery'])) {
                $this->updateSubQuery($input, $queryDetails['subQuery']);
            }
        }
    }

    /**
     * Generates Params for statement to execute.
     *
     * @param array $queryPayload
     * @param array $input
     * @return array
     */
    private function getStmtParams($queryPayload, $input)
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
     * @param int   $dataCount        Number of records to validate as received in payload
     * @param array $data             Payload data
     * @param array $validationConfig Validation configuration.
     * @return void
     */
    private function validate($data, $validationConfig)
    {
        if (is_null($this->validationObj)) {
            $this->validationObj = new Validate();
        }
        foreach ($validationConfig as &$v) {
            if (!$validationObj->$v['fn']($data[$v['dataKey']])) {
                return ['data' => $data, 'Error' => $v['errorMessage']];
            }
        }
        return true;
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
