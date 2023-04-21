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
    public $authorize = null;

    public static function init()
    {
        (new self)->process();
    }

    public function process()
    {
        $this->authorize = new Authorize();
        $this->authorize->init();
        $this->clientDB = getenv($this->authorize->clientDatabase);

        $this->db = new Database(
            $this->authorize->clientHostname,
            $this->authorize->clientUsername,
            $this->authorize->clientPassword,
            $this->authorize->clientDatabase
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
    function processHttpGET()
    {
        // Load uriParams
        $uriParams = $this->authorize->routeParams;

        // Load Read Only Session
        $readOnlySession = $this->authorize->readOnlySession;

        // Load Queries
        $queries = include $this->authorize->__file__;
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
    function processHttpPOST()
    {
        // Load uriParams
        $uriParams = $this->authorize->routeParams;

        // Load Read Only Session
        $readOnlySession = $this->authorize->readOnlySession;

        // Load Payload
        parse_str(file_get_contents('php://input'), $payload);
        $payload['data'] = json_decode($payload['data'], true);
        $isAssoc = $this->isAssoc($payload['data']);
        if ($isAssoc) {
            $payload['data'] = [$payload['data']];
        }
        $dataCount = count($payload['data']);

        // Load Config
        $config = include $this->authorize->__file__;

        // Required validations.
        if (isset($config['validate'])) {
            $this->validate($dataCount, $payload['data'], $config['validate']);
        }

        // Perform action
        for ($i = 0; $i < $dataCount; $i++) {
            foreach ($config['queries'] as $key => &$value) {
                $stmt = $this->db->insert($value['query']);
                $payloadParamValues = [];
                foreach ($value['payload'] as $p) {
                    $payloadParamValues[] = $payload['data'][$i][$p];
                }
                $stmt->execute($payloadParamValues);
                $insertId = $this->db->lastInsertId();
                if (isset($config['queries']['insertId'])) {
                    $params[$config['queries']['insertId']] = $insertId;
                }
                $stmt->closeCursor();
                if (isset($config['queries'][$key]['subQuery'])) {
                    $this->insertSubQuery($params, $config['queries'][$key]['subQuery']);
                }
            }
        }
    }

    /**
     * Process HTTP PUT request
     *
     * @return void
     */
    function processHttpPUT()
    {
        $this->processHttpUpdate();
    }

    /**
     * Process HTTP PATCH request
     *
     * @return void
     */
    function processHttpPATCH()
    {
        $this->processHttpUpdate();
    }

    /**
     * Process HTTP DELETE request
     *
     * @return void
     */
    function processHttpDELETE()
    {
        $this->processHttpUpdate();
    }
    
    function processHttpUpdate()
    {
        // Load uriParams
        $uriParams = $this->authorize->routeParams;

        // Load Read Only Session
        $readOnlySession = $this->authorize->readOnlySession;

        // Load Payload
        parse_str(file_get_contents('php://input'), $payload);
        $isAssoc = $this->isAssoc($payload['data']);
        if ($isAssoc) {
            $payload['data'] = [$payload['data']];
        }
        $dataCount = count($payload['data']);

        // Load Config
        $config = include $this->authorize->__file__;

        // Required validations.
        if (isset($config['validate'])) {
            $this->validate($dataCount, $payload['data'], $config['validate']);
        }

        // Perform action
        for ($i = 0; $i < $dataCount; $i++) {
            foreach ($config['queries'] as $key => &$value) {
                $stmt = $this->db->update($value['query']);
                $stmt->execute($value['payload']);
                $stmt->closeCursor();
                if (isset($config['queries'][$key]['subQuery'])) {
                    $this->updateSubQuery($params, $config['queries'][$key]['subQuery']);
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
    private function validate($dataCount, $data, $validationConfig)
    {
        $validate = new Validate();
        for ($i = 0; $i < $dataCount; $i++) {
            foreach ($validationConfig as &$v) {
                if (!$validate->$v['fn']($data[$i][$v['dataKey']])) {
                    HttpErrorResponse::return404($v['errorMessage']);
                }
            }
        }
        $validate = null;
    }

    /**
     * Function to insert sub queries recursively.
     *
     * @param array $params   Dynamic params values like insert ids
     * @param array $subQuery N level Sub Query array.
     * @return void
     */
    private function insertSubQuery(&$params, &$subQuery)
    {
        foreach ($subQuery as $key => &$value) {
            $stmt = $this->db->insert($value['query']);
            foreach ($value['payload'] as $k => $v) {
                if (isset($params[$v])) {
                    $value['payload'][$k] = $params[$v];
                }
            }
            $stmt->execute($value['payload']);
            $insertId = $connection->lastInsertId();
            if (isset($subQuery['insertId'])) {
                $params[$subQuery['insertId']] = $insertId;
            }
            $stmt->closeCursor();
            if (isset($params[$key]['subQuery'])) {
                $this->insertSubQuery($params, $params[$key]['subQuery']);
            }
        }
    }

    /**
     * Function to update sub queries recursively.
     *
     * @param array $subQuery N level Sub Query array.
     * @return void
     */
    private function updateSubQuery(&$subQuery)
    {
        foreach ($subQuery as $key => &$value) {
            $stmt = $this->db->insert($value['query']);
            $stmt->execute($value['payload']);
            $stmt->closeCursor();
            if (isset($params[$key]['subQuery'])) {
                $this->updateSubQuery($params[$key]['subQuery']);
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
