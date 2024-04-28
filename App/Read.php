<?php
namespace App;

use App\AppTrait;
use App\HttpRequest;
use App\HttpResponse;
use App\JsonEncode;
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
    public $jsonEncodeObj = null;

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
        $this->jsonEncodeObj = JsonEncode::getObject();

        // Load Queries
        $readSqlConfig = include HttpRequest::$__file__;
        
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
                    if (!$this->isAssoc($readSqlDetails['subQuery'])) {
                        HttpResponse::return5xx(501, 'Invalid Configuration: subQuery should be associative array');
                    }
                    $this->readDB($readSqlDetails['subQuery'], false);
                }
                if ($readSqlDetails['mode'] === 'singleRowFormat' && isset($readSqlDetails['subQuery'])) {
                    $this->jsonEncodeObj->endAssoc();
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
            $this->jsonEncodeObj->startAssoc();
        } else {
            $this->jsonEncodeObj->startAssoc($parentKey);
        }
        foreach($row as $key => $value) {
            $this->jsonEncodeObj->addKeyValue($key, $value);
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
        $this->jsonEncodeObj->addKeyValue('page', HttpRequest::$input['payload']['page']);
        $this->jsonEncodeObj->addKeyValue('perpage', HttpRequest::$input['payload']['perpage']);
        $this->jsonEncodeObj->addKeyValue('totalPages', $totalPages);
        $this->jsonEncodeObj->addKeyValue('totalRecords', $totalRowsCount);
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
                $this->jsonEncodeObj->startAssoc();
                $this->fetchRowsCount($start, $parentKey, $readSqlDetails);
                $this->jsonEncodeObj->startArray('data');
            } else {
                $this->jsonEncodeObj->startArray();
            }
        } else {
            $this->jsonEncodeObj->startArray($parentKey);
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
                $this->jsonEncodeObj->encode($row[key($row)]);
            } else {
                $this->jsonEncodeObj->encode($row);
            }
        }
        $this->jsonEncodeObj->endArray();
        if ($start) {
            if (isset($readSqlDetails['countQuery'])) {
                $this->jsonEncodeObj->endAssoc();
            }
        }
        $this->db->closeCursor();
    }    
}
