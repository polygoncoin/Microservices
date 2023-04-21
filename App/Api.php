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
        // Load uriParams
        $uriParams = $this->authorizeObj->routeParams;

        // Load Read Only Session
        $readOnlySession = $this->authorizeObj->readOnlySession;

        // Load Queries
        $queries = include $this->authorizeObj->__file__;
        $jsonEncode = new JsonEncode();
        if (isset($queries['default'])) {
            $stmt = $this->db->select($queries['default']['query']);
            $stmt->execute($queries['default']['payload']);
            if ($queries['default']['mode'] === 'singleRowFormat') {
                if (count($queries) === 1) {
                    $jsonEncode->encode($stmt->fetch(\PDO::FETCH_ASSOC));
                } else {
                    $jsonEncode->startAssoc();
                    foreach($stmt->fetch(\PDO::FETCH_ASSOC) as $key => $value) {
                        $jsonEncode->addKeyValue($key, $value);
                    }
                }
                $stmt->closeCursor();
            }
            if ($queries['default']['mode'] === 'multipleRowFormat') {
                $jsonEncode->startArray();
                for (;$row=$stmt->fetch(\PDO::FETCH_ASSOC);) {
                    $jsonEncode->encode($row);
                }
                $jsonEncode->endArray();
                $stmt->closeCursor();
                return;
            }
        }
        if (isset($queries['default']['mode']) && $queries['default']['mode'] === 'singleRowFormat') {
            foreach ($queries as $key => &$value) {
                if ($key === 'default') continue;
                $stmt = $this->db->select($value['query']);
                $stmt->execute($value['payload']);
                if ($queries[$key]['mode'] === 'singleRowFormat') {
                    $jsonEncode->addKeyValue($key, $stmt->fetch(\PDO::FETCH_ASSOC));
                }
                if ($queries[$key]['mode'] === 'multipleRowFormat') {
                    $jsonEncode->startArray($key);
                    $jsonEncode->addValue($stmt->fetch(\PDO::FETCH_ASSOC));
                    $jsonEncode->endArray($key);
                }
                $stmt->closeCursor();
            }
            if (count($queries) > 1) {
                $jsonEncode->endAssoc();
            }
        }
        $jsonEncode = null;
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
                    foreach ($queryDetails['payload'] as $var => [$type, $typeKey]) {
                        $stmtParams[$var] = $input[$type][$typeKey];
                    }
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
                    $stmtParams = [];
                    foreach ($queryDetails['payload'] as $var => [$type, $typeKey]) {
                        $stmtParams[$var] = $input[$type][$typeKey];
                    }
                    $__SET__ = implode(', ',array_map(function ($v) { return '`' . str_replace('`','',$v) . '` = ?';}, array_keys($stmtParams)));
                    $query = str_replace('__SET__', 'SET ' . $__SET__, $queryDetails['query']);
                    $stmt = $this->db->insert($query);
                    $stmt->execute(array_values($stmtParams));
                    $stmt->closeCursor();
                    if (isset($config['queries'][$key]['subQuery'])) {
                        $this->updateSubQuery($input, $config['queries'][$key]['subQuery']);
                    }
                }
            }
        }
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
     * Function to insert sub queries recursively.
     *
     * @param array $params   Dynamic params values like insert ids
     * @param array $subQuery N level Sub Query array.
     * @return void
     */
    private function insertSubQuery(&$input, &$subQuery)
    {
        foreach ($subQuery as $key => &$queryDetails) {
            $stmtParams = [];
            foreach ($queryDetails['payload'] as $var => [$type, $typeKey]) {
                $stmtParams[$var] = $input[$type][$typeKey];
            }
            $__SET__ = implode(', ',array_map(function ($v) { return '`' . str_replace('`','',$v) . '` = ?';}, array_keys($stmtParams)));
            $query = str_replace('__SET__', 'SET ' . $__SET__, $queryDetails['query']);
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
     * Function to update sub queries recursively.
     *
     * @param array $subQuery N level Sub Query array.
     * @return void
     */
    private function updateSubQuery(&$input, &$subQuery)
    {
        foreach ($subQuery as $key => &$queryDetails) {
            $stmtParams = [];
            foreach ($queryDetails['payload'] as $var => [$type, $typeKey]) {
                $stmtParams[$var] = $input[$type][$typeKey];
            }
            $__SET__ = implode(', ',array_map(function ($v) { return '`' . str_replace('`','',$v) . '` = ?';}, array_keys($stmtParams)));
            $query = str_replace('__SET__', 'SET ' . $__SET__, $queryDetails['query']);
            $stmt = $this->db->insert($query);
            $stmt->execute(array_values($stmtParams));
            $stmt->closeCursor();
            if (isset($queryDetails['subQuery'])) {
                $this->updateSubQuery($input, $queryDetails['subQuery']);
            }
        }
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
