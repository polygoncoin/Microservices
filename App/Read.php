<?php
namespace App;

use App\AppTrait;
use App\HttpRequest;
use App\HttpResponse;
use App\Servers\Database\Database;

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
        $this->globalDB = getenv('globalDbName');
        $this->clientDB = getenv(HttpRequest::$clientDatabase);
        $this->db = Database::getObject();
        $this->jsonObj = HttpResponse::getJsonObject();

        // Load Queries
        $readSqlConfig = include HttpRequest::$__file__;
        
        // Set required fields.
        HttpRequest::$input['requiredPayload'] = $this->getRequired($readSqlConfig);

        // Start Read operation.
        $this->readDB($readSqlConfig);
    }

    /**
     * Function to select sub queries recursively.
     *
     * @param array $readSqlConfig Config from file
     * @param bool  $start         true to represent the first call in recursion.
     * @return void
     */
    private function readDB(&$readSqlConfig, $start = true)
    {
        $readSqlConfig = ($start) ? [$readSqlConfig] : $readSqlConfig;
        foreach ($readSqlConfig as $key => &$readSqlDetails) {
            if ($this->isAssoc($readSqlDetails)) {
                switch ($readSqlDetails['mode']) {
                    case 'singleRowFormat':
                        $this->fetchSingleRow($start, $key, $readSqlDetails);
                        break;
                    case 'multipleRowFormat':
                        $this->fetchMultipleRows($start, $key, $readSqlDetails);
                        break;
                }
                if (isset($readSqlDetails['subQuery'])) {
                    $this->readDB($readSqlDetails['subQuery'], false);
                }
                if ($readSqlDetails['mode'] === 'singleRowFormat' && isset($readSqlDetails['subQuery'])) {
                    $this->jsonObj->endAssoc();
                }
            }
        }
    }

    /**
     * Function to fetch single record.
     *
     * @param bool  $start          true to represent the first call in recursion.
     * @param bool  $parentKey      Associative array keyy.
     * @param bool  $readSqlDetails Read SQL configuration.
     * @return void
     */
    private function fetchSingleRow(&$start, &$parentKey, &$readSqlDetails)
    {
        list($sql, $sqlParams) = $this->getSqlAndParams($readSqlDetails);
        $this->db->execDbQuery($sql, $sqlParams);
        if ($row = $this->db->fetch()) {
            ;
        } else {
            $row = [];
        }
        if (isset($readSqlDetails['subQuery'])) {
            $subQueryKeys = array_keys($readSqlDetails['subQuery']);
            foreach($row as $key => $value) {
                if (in_array($key, $subQueryKeys)) {
                    HttpResponse::return5xx(501, 'Invalid configuration: Conflicting column names');
                }
            }
        }
        if ($start) {
            $this->jsonObj->startAssoc();
        } else {
            $this->jsonObj->startAssoc($parentKey);
        }
        foreach($row as $key => $value) {
            $this->jsonObj->addKeyValue($key, $value);
        }
        $this->db->closeCursor();
    }

    /**
     * Function to fetch row count.
     *
     * @param bool  $start          true to represent the first call in recursion.
     * @param bool  $parentKey      Associative array keyy.
     * @param bool  $readSqlDetails Read SQL configuration.
     * @return void
     */
    private function fetchRowsCount(&$start, &$parentKey, &$readSqlDetails)
    {
        $readSqlDetailsCount = $readSqlDetails;
        $readSqlDetailsCount['query'] = $readSqlDetailsCount['countQuery'];
        unset($readSqlDetailsCount['countQuery']);
        HttpRequest::$input['payload']['page']  = $_GET['page'] ?? 1;
        HttpRequest::$input['payload']['perpage']  = $_GET['perpage'] ?? 10;
        if (HttpRequest::$input['payload']['perpage'] > getenv('maxPerpage')) {
            HttpResponse::return4xx(403, 'perpage exceeds max perpage value of '.getenv('maxPerpage'));
        }
        HttpRequest::$input['payload']['start']  = (HttpRequest::$input['payload']['page'] - 1) * HttpRequest::$input['payload']['perpage'];
        list($sql, $sqlParams) = $this->getSqlAndParams($readSqlDetailsCount);
        $this->db->execDbQuery($sql, $sqlParams);
        $row = $this->db->fetch();
        $this->db->closeCursor();
        $totalRowsCount = $row['count'];
        $totalPages = ceil($totalRowsCount/HttpRequest::$input['payload']['perpage']);
        $this->jsonObj->addKeyValue('page', HttpRequest::$input['payload']['page']);
        $this->jsonObj->addKeyValue('perpage', HttpRequest::$input['payload']['perpage']);
        $this->jsonObj->addKeyValue('totalPages', $totalPages);
        $this->jsonObj->addKeyValue('totalRecords', $totalRowsCount);
    }
    
    /**
     * Function to fetch multiple record.
     *
     * @param bool  $start          true to represent the first call in recursion.
     * @param bool  $parentKey      Associative array keyy.
     * @param bool  $readSqlDetails Read SQL configuration.
     * @return void
     */
    private function fetchMultipleRows(&$start, &$parentKey, &$readSqlDetails)
    {
        if (isset($readSqlDetails['subQuery'])) {
            HttpResponse::return5xx(501, 'Invalid Configuration: multipleRowFormat can\'t have sub query');
        }
        if ($start) {
            if (isset($readSqlDetails['countQuery'])) {
                $this->jsonObj->startAssoc();
                $this->fetchRowsCount($start, $parentKey, $readSqlDetails);
                $this->jsonObj->startArray('data');
            } else {
                $this->jsonObj->startArray();
            }
        } else {
            $this->jsonObj->startArray($parentKey);
        }
        list($sql, $sqlParams) = $this->getSqlAndParams($readSqlDetails);
        if ($start) {
            if (isset($readSqlDetails['countQuery'])) {
                $start = HttpRequest::$input['payload']['start'];
                $offset = HttpRequest::$input['payload']['perpage'];
                $sql .= " LIMIT {$start}, {$offset}";
            }
        }
        $this->db->execDbQuery($sql, $sqlParams);
        $singleColumn = false;
        for ($i=0;$row=$this->db->fetch();) {
            if ($i===0) {
                if(count($row) === 1) {
                    $singleColumn = true;
                }
                $i++;
            }
            if ($singleColumn) {
                $this->jsonObj->encode($row[key($row)]);
            } else {
                $this->jsonObj->encode($row);
            }
        }
        $this->jsonObj->endArray();
        if ($start) {
            if (isset($readSqlDetails['countQuery'])) {
                $this->jsonObj->endAssoc();
            }
        }
        $this->db->closeCursor();
    }    
}
