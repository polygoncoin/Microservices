<?php
namespace App;

use App\Authorize;
use App\Servers\Database;
use App\JsonEncode;

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
        $this->clientDB = $this->authorize->clientDatabase;

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
            $sth = $this->db->select($queries['default'][0]);
            $sth->execute($queries['default'][1]);
            if ($queries['default'][2] === 'singleRowFormat') {
                $jsonEncode->startAssoc();
                foreach($sth->fetch(\PDO::FETCH_ASSOC) as $key => $value) {
                    $jsonEncode->addKeyValue($key, $value);
                }
                $sth->closeCursor();
            }
            if ($queries['default'][2] === 'multipleRowFormat') {
                $jsonEncode->startArray();
                for (;$row=$sth->fetch(\PDO::FETCH_ASSOC);) {
                    $jsonEncode->encode($row);
                }
                $jsonEncode->endArray();
                $sth->closeCursor();
                return;
            }
        }
        if (isset($queries['default'][2]) && $queries['default'][2] === 'singleRowFormat') {
            foreach ($queries as $key => &$value) {
                if ($key === 'default') continue;
                $sth = $this->db->select($value[0]);
                $sth->execute($value[1]);
                if ($queries[$key][2] === 'singleRowFormat') {
                    $jsonEncode->addKeyValue($key, $sth->fetch(\PDO::FETCH_ASSOC));
                }
                if ($queries[$key][2] === 'multipleRowFormat') {
                    $jsonEncode->startArray($key);
                    $jsonEncode->addValue($sth->fetch(\PDO::FETCH_ASSOC));
                    $jsonEncode->endArray($key);
                }
                $sth->closeCursor();
            }
            $jsonEncode->endAssoc();
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
        $uriParams = $this->routeParams;

        // Load Read Only Session
        $readOnlySession = $this->readOnlySession;

        // Load Payload
        parse_str(file_get_contents('php://input'), $payload);
        $dataCount = ($this->isAssoc($payload['data'])) ? 1 : count($payload['data']);
        if ($dataCount === 1) {
            $payload['data'] = [$payload['data']];
        }

        // Load Config
        $config = include $this->__file__;

        // Required validations.
        if (isset($config['validate'])) {
            $this->validate($dataCount, $payload['data'], $config['validate']);
        }

        // Perform action
        for ($i = 0; $i < $dataCount; $i++) {
            foreach ($config['queries'] as $key => &$value) {
                $sth = $this->db->insert($value['query']);
                $sth->execute($value['payload']);
                $insertId = $connection->lastInsertId();
                if (isset($config['queries']['insertId'])) {
                    $params[$config['queries']['insertId']] = $insertId;
                }
                $sth->closeCursor();
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
        $uriParams = $this->routeParams;

        // Load Read Only Session
        $readOnlySession = $this->readOnlySession;

        // Load Payload
        parse_str(file_get_contents('php://input'), $payload);
        $dataCount = ($this->isAssoc($payload['data'])) ? 1 : count($payload['data']);
        if ($dataCount === 1) {
            $payload['data'] = [$payload['data']];
        }

        // Load Config
        $config = include $this->__file__;

        // Required validations.
        if (isset($config['validate'])) {
            $this->validate($dataCount, $payload['data'], $config['validate']);
        }

        // Perform action
        for ($i = 0; $i < $dataCount; $i++) {
            foreach ($config['queries'] as $key => &$value) {
                $sth = $this->db->update($value['query']);
                $sth->execute($value['payload']);
                $sth->closeCursor();
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
        for ($i = 0; $i < $dataCount; $i++) {
            foreach ($validationConfig as &$v) {
                if (!App\Validation\Validate::$v['fn']($data[$i][$v['dataKey']])) {
                    HttpErrorResponse::return404($v['errorMessage']);
                }
            }
        }
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
            $sth = $this->db->insert($value['query']);
            foreach ($value['payload'] as $k => $v) {
                if (isset($params[$v])) {
                    $value['payload'][$k] = $params[$v];
                }
            }
            $sth->execute($value['payload']);
            $insertId = $connection->lastInsertId();
            if (isset($subQuery['insertId'])) {
                $params[$subQuery['insertId']] = $insertId;
            }
            $sth->closeCursor();
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
            $sth = $this->db->insert($value['query']);
            $sth->execute($value['payload']);
            $sth->closeCursor();
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
