<?php
namespace App;

use App\AppTrait;
use App\Constants;
use App\Env;
use App\HttpRequest;
use App\HttpResponse;
use App\Servers\Database\Database;
use App\Validation\Validator;

/**
 * Class to initialize DB Read operation
 *
 * This class process the GET api request
 *
 * @category   CRUD Read
 * @package    Microservices
 * @author     Ramesh Narayan Jangid
 * @copyright  Ramesh Narayan Jangid
 * @version    Release: @1.0.0@
 * @since      Class available since Release 1.0.0
 */
class Read
{
    use AppTrait;

    /**
     * DB Server connection object
     *
     * @var object
     */
    public $db = null;

    /**
     * JsonEncode class object
     *
     * @var object
     */
    public $jsonObj = null;

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
        $this->jsonObj = HttpResponse::getJsonObject();
        return HttpResponse::isSuccess();
    }

    /**
     * Process
     *
     * @return boolean
     */
    public function process()
    {
        $Constants = 'App\\Constants';
        $Env = 'App\\Env';
        $HttpRequest = 'App\\HttpRequest';
        // Load Queries
        $readSqlConfig = include HttpRequest::$__file__;
        // Use results in where clause of sub queries recursively.
        $useHierarchy = $this->getUseHierarchy($readSqlConfig);
        if (
            Env::$allowConfigRequest &&
            Env::$isConfigRequest
        ) {
            $this->processReadConfig($readSqlConfig, $useHierarchy);
        } else {
            $this->processRead($readSqlConfig, $useHierarchy);
        }
        return HttpResponse::isSuccess();
    }

    /**
     * Process read function for configuration.
     *
     * @param array $readSqlConfig Config from file
     * @param bool  $useHierarchy  Use results in where clause of sub queries recursively.
     * @return boolean
     */
    private function processReadConfig(&$readSqlConfig, $useHierarchy)
    {
        return HttpResponse::isSuccess();
    }    

    /**
     * Process Function for read operation.
     *
     * @param array $readSqlConfig Config from file
     * @param bool  $useHierarchy  Use results in where clause of sub queries recursively.
     * @return boolean
     */
    private function processRead(&$readSqlConfig, $useHierarchy)
    {
        HttpRequest::$input['requiredArr'] = $this->getRequired($readSqlConfig, true, $useHierarchy);
        HttpRequest::$input['required'] = HttpRequest::$input['requiredArr']['__required__'];
        HttpRequest::$input['payload'] = HttpRequest::$input['payloadArr'][0];
        // Start Read operation.
        $keys = [];
        $this->readDB($readSqlConfig, true, $keys, $useHierarchy);        
        return HttpResponse::isSuccess();
    }

    /**
     * Function to select sub queries recursively.
     *
     * @param array $readSqlConfig Config from file
     * @param bool  $start         true to represent the first call in recursion.
     * @param array $keys          Keys in recursion.
     * @param bool  $useHierarchy  Use results in where clause of sub queries recursively.
     * @return boolean
     */
    private function readDB(&$readSqlConfig, $start, &$keys, $useHierarchy)
    {
        $success = HttpResponse::isSuccess();
        if (!$success) {
            return $success;
        }
        $isAssoc = $this->isAssoc($readSqlConfig);
        if ($isAssoc) {
            switch ($readSqlConfig['mode']) {
                case 'singleRowFormat':
                    if ($start) {
                        $this->jsonObj->startObject('Results');
                    } else {
                        $this->jsonObj->startObject();
                    }
                    $this->fetchSingleRow($readSqlConfig, $keys, $useHierarchy);
                    $this->jsonObj->endObject();
                    break;
                case 'multipleRowFormat':
                    $keysCount = count($keys)-1;
                    if ($start) {
                        if (isset($readSqlConfig['countQuery'])) {
                            $this->fetchRowsCount($readSqlConfig);
                        }
                        $this->jsonObj->startArray('Results');
                    } else {
                        $this->jsonObj->startArray($keys[$keysCount]);
                    }
                    $this->fetchMultipleRows($readSqlConfig, $keys, $useHierarchy);
                    $this->jsonObj->endArray();
                    if (!$start) {
                        $this->jsonObj->endObject();
                    }
                    break;
            }
            if (!$useHierarchy && isset($readSqlConfig['subQuery'])) {
                $this->callReadDB($readSqlConfig, $keys, $row, $useHierarchy);
            }
        }
        return HttpResponse::isSuccess();
    }

    /**
     * Function to fetch single record.
     *
     * @param array $readSqlConfig Read SQL configuration.
     * @param array $keys          Module Keys in recursion.
     * @param bool  $useHierarchy  Use results in where clause of sub queries recursively.
     * @return boolean
     */
    private function fetchSingleRow(&$readSqlConfig, &$keys, $useHierarchy)
    {
        $success = HttpResponse::isSuccess();
        if (!$success) {
            return $success;
        }
        $isAssoc = $this->isAssoc($readSqlConfig);
        list($sql, $sqlParams, $errors) = $this->getSqlAndParams($readSqlConfig);
        if (!empty($errors)) {
            return;
        }
        $this->db->execDbQuery($sql, $sqlParams);
        if ($row = $this->db->fetch()) {
            //check if selected column-name mismatches or confliects with configured module/submodule names.
            if (isset($readSqlConfig['subQuery'])) {
                $subQueryKeys = array_keys($readSqlConfig['subQuery']);
                foreach($row as $key => $value) {
                    if (in_array($key, $subQueryKeys)) {
                        HttpResponse::return5xx(501, 'Invalid configuration: Conflicting column names');
                        return;
                    }
                }
            }
        } else {
            $row = [];
        }
        foreach($row as $key => $value) {
            $this->jsonObj->addKeyValue($key, $value);
        }
        $this->db->closeCursor();
        if ($useHierarchy && isset($readSqlConfig['subQuery'])) {
            $this->callReadDB($readSqlConfig, $keys, $row, $useHierarchy);
        }
        return HttpResponse::isSuccess();
    }

    /**
     * Function to fetch row count.
     *
     * @param array  $readSqlConfig Read SQL configuration.
     * @return boolean
     */
    private function fetchRowsCount($readSqlConfig)
    {
        $success = HttpResponse::isSuccess();
        if (!$success) {
            return $success;
        }
        $readSqlConfig['query'] = $readSqlConfig['countQuery'];
        unset($readSqlConfig['countQuery']);
        HttpRequest::$input['payload']['page']  = $_GET['page'] ?? 1;
        HttpRequest::$input['payload']['perpage']  = $_GET['perpage'] ?? 10;
        if (HttpRequest::$input['payload']['perpage'] > Env::$maxPerpage) {
            HttpResponse::return4xx(403, 'perpage exceeds max perpage value of '.Env::$maxPerpage);
            return;
        }
        HttpRequest::$input['payload']['start']  = (HttpRequest::$input['payload']['page'] - 1) * HttpRequest::$input['payload']['perpage'];
        list($sql, $sqlParams, $errors) = $this->getSqlAndParams($readSqlConfig);
        if (!empty($errors)) {
            return;
        }
        $this->db->execDbQuery($sql, $sqlParams);
        $row = $this->db->fetch();
        $this->db->closeCursor();
        $totalRowsCount = $row['count'];
        $totalPages = ceil($totalRowsCount/HttpRequest::$input['payload']['perpage']);
        $this->jsonObj->addKeyValue('page', HttpRequest::$input['payload']['page']);
        $this->jsonObj->addKeyValue('perpage', HttpRequest::$input['payload']['perpage']);
        $this->jsonObj->addKeyValue('totalPages', $totalPages);
        $this->jsonObj->addKeyValue('totalRecords', $totalRowsCount);
        return HttpResponse::isSuccess();
    }
    
    /**
     * Function to fetch multiple record.
     *
     * @param array $readSqlConfig Read SQL configuration.
     * @param array $keys          Module Keys in recursion.
     * @param bool  $useHierarchy  Use results in where clause of sub queries recursively.
     * @return boolean
     */
    private function fetchMultipleRows(&$readSqlConfig, &$keys, $useHierarchy)
    {
        $success = HttpResponse::isSuccess();
        if (!$success) {
            return $success;
        }
        $isAssoc = $this->isAssoc($readSqlConfig);
        if (!$useHierarchy && isset($readSqlConfig['subQuery'])) {
            HttpResponse::return5xx(501, 'Invalid Configuration: multipleRowFormat can\'t have sub query');
            return;
        }
        $isAssoc = $this->isAssoc($readSqlConfig);
        list($sql, $sqlParams, $errors) = $this->getSqlAndParams($readSqlConfig);
        if (!empty($errors)) {
            return;
        }
        if (isset($readSqlConfig['countQuery'])) {
            $start = HttpRequest::$input['payload']['start'];
            $offset = HttpRequest::$input['payload']['perpage'];
            $sql .= " LIMIT {$start}, {$offset}";
        }
        $singleColumn = false;
        $stmt = $this->db->prepare($sql);
        if (!$stmt) {
            HttpResponse::return5xx(501, 'Invalid database query');
            return;
        }
        $stmt->execute($sqlParams);
        for ($i=0;$row=$stmt->fetch(\PDO::FETCH_ASSOC);) {
            if ($i===0) {
                if (count($row) === 1) {
                    $singleColumn = true;
                }
                $singleColumn = $singleColumn && !isset($readSqlConfig['subQuery']);
                $i++;
            }
            if ($singleColumn) {
                $this->jsonObj->encode($row[key($row)]);
            } else if (isset($readSqlConfig['subQuery'])) {
                $this->jsonObj->startObject();
                foreach($row as $key => $value) {
                    $this->jsonObj->addKeyValue($key, $value);
                }
            } else {
                $this->jsonObj->encode($row);
            }
            if ($useHierarchy && isset($readSqlConfig['subQuery'])) {
                $this->callReadDB($readSqlConfig, $keys, $row, $useHierarchy);
            }
        }
        $stmt->closeCursor();
        return HttpResponse::isSuccess();
    }    

    /**
     * Function to reset data for module key wise.
     *
     * @param array $keys         Module Keys in recursion.
     * @param array $row          Row data fetched from DB.
     * @param bool  $useHierarchy Use results in where clause of sub queries recursively.
     * @return boolean
     */
    private function resetFetchData(&$keys, $row, $useHierarchy)
    {
        $success = HttpResponse::isSuccess();
        if (!$success) {
            return $success;
        }
        if ($useHierarchy) {
            if (count($keys) === 0) {
                HttpRequest::$input['hierarchyData'] = [];
                HttpRequest::$input['hierarchyData']['return'] = [];
            }
            $httpReq = &HttpRequest::$input['hierarchyData']['return'];
            foreach ($keys as $k) {
                if (!isset($httpReq[$k])) {
                    $httpReq[$k] = [];
                }
                $httpReq = &$httpReq[$k];
            }
            $httpReq = $row;
        }
        return HttpResponse::isSuccess();
    }

    /**
     * Validate and call readDB
     *
     * @param array $readSqlConfig Read SQL configuration.
     * @param array $keys          Module Keys in recursion.
     * @param array $row           Row data fetched from DB.
     * @param bool  $useHierarchy  Use results in where clause of sub queries recursively.
     * @return boolean
     */
    private function callReadDB(&$readSqlConfig, &$keys, &$row, $useHierarchy)
    {
        $success = HttpResponse::isSuccess();
        if (!$success) {
            return $success;
        }
        if ($useHierarchy) {
            $this->resetFetchData($keys, $row, $useHierarchy);
        }
        if (isset($readSqlConfig['subQuery']) && $this->isAssoc($readSqlConfig['subQuery'])) {
            foreach ($readSqlConfig['subQuery'] as $subQuery_key => $readSqlDetails) {
                $k = array_merge($keys, [$subQuery_key]);
                $_useHierarchy = ($useHierarchy) ?? $this->getUseHierarchy($readSqlDetails);
                $this->readDB($readSqlDetails, false, $k, $_useHierarchy);
            }
        }
        return HttpResponse::isSuccess();
    }
}
