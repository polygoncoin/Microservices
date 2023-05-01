<?php
namespace App;

use App\JsonEncode;
use App\Servers\Database;

/**
 * Class for DB Migration
 *
 * This class replicate clinet_master to a new database.
 *
 * @category   DB Migration
 * @package    Microservices
 * @author     Ramesh Narayan Jangid
 * @copyright  Ramesh Narayan Jangid
 * @version    Release: @1.0.0@
 * @since      Class available since Release 1.0.0
 */
class Migration
{
    /**
     * Global Database
     *
     * @var string
     */
    const globalDbName = 'global';

    /**
     * Client master Database
     *
     * @var string
     */
    const clientMasterDbName = 'client_master';

    /**
     * DB Server connection object
     *
     * @var object
     */
    public $db = null;

    /**
     * New DB Server connection object
     *
     * @var object
     */
    public $newDbObj = null;

    /**
     * New DB Server database
     *
     * @var string
     */
    public $newDb = null;

    /**
     * Authorize class object
     *
     * @var object
     */
    public $authorizeObj = null;

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

        $this->processMigration();
    }

    /**
     * Process update request
     *
     * @return void
     */
    private function processMigration()
    {
        $destinationDbId = $_GET['id'];
        
        $this->db = new Database(
            'defaultDbHostname',
            'defaultDbUsername',
            'defaultDbPassword',
            'defaultDbDatabase'
        );

        try {
            $query = "SELECT * from `m004_master_connection` WHERE id = ? and is_deleted = 'No'";
            $stmt = $this->db->getStatement($query);
            $stmt->execute([$destinationDbId]);
            $dbDetails = $stmt->fetch(\PDO::FETCH_ASSOC);
            $stmt->closeCursor();
        } catch(\PDOException $e) {
            HttpErrorResponse::return501('Database error: ' . $e->getMessage());
        }

        $this->newDbObj = new Database(
            $dbDetails['db_hostname'],
            $dbDetails['db_username'],
            $dbDetails['db_password'],
            null
        );
        $this->newDb = getenv($dbDetails['db_database']);
        $result = [];
        $this->createDatabase($result);
        $this->newDbObj->useDatabase($this->newDb);
        $tables = $this->createTables($result);
        foreach ($tables as $tableName) {
            $result[] = $this->copyTableData($tableName);
        }
        $this->createViews($result);
        $this->createFunctions($result);
        $this->createSps($result);

        $this->jsonEncodeObj = new JsonEncode();
        $this->jsonEncodeObj->encode(['Status' => 200, 'Message' => $result]);
        $this->jsonEncodeObj = null;
    }
    
    function createDatabase(&$result)
    {
        $this->newDbObj->pdo->exec("CREATE DATABASE IF NOT EXISTS `{$this->newDb}`");
        $result[] = "Database `{$this->newDb}` created";
    }
    
    function createTables(&$result)
    {
        $tables = array_column($this->db->pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN), 0);
        foreach ($tables as $tableName) {
            $createCommand = $this->db->pdo->query("SHOW CREATE TABLE `{$sourceDbName}`.`{$tableName}`")->fetchColumn(1);
            
            $this->newDbObj->exec($createCommand);
            $result[] = "Table `{$tableName}` created";
        }
        return $tables;
    }

    function copyTableData($tableName)
    {
        try {
            $query = 'SELECT * FROM `'.self::$clientMasterDbName."`.`{$tableName}`";
            $stmt = $this->db->getStatement($query);
            $stmt->execute([$destinationDbId]);
            $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            $stmt->closeCursor();
            $sql = null;
            foreach ($rows as $row) {
                if (is_null($sql)) {
                    $comma = '';
                    $sql = "INSERT INTO `{$tableName}` SET";
                    foreach ($row as $key => $value) {
                        $sql .= $comma . PHP_EOL . "`{$key}` = ?";
                        $comma = ',';
                    }
                    $s = $this->newDbObj->getStatement($sql);
                }
                $S->execute(array_values($row));
            }
            $s->closeCursor();
            return "Data for table `{$tableName}` copied";
        } catch(\PDOException $e) {
            HttpErrorResponse::return501('Database error: ' . $e->getMessage());
        }
    }

    function createViews(&$result)
    {
        $views = array_column(
            $this->db->pdo->query("SHOW FULL TABLES WHERE table_type = 'VIEW'")->fetchAll(PDO::FETCH_NUM), 0);
        foreach ($views as $view) {
            $viewCreateCommand = $this->db->pdo->query("SHOW CREATE VIEW `{$view}`")->fetchColumn(1);
            $this->newDbObj->pdo->exec($viewCreateCommand);
            $result[] = "View `{$view}` created";
        }
    }


    function createFunctions(&$result)
    {
        $query = "SELECT
                routine_name
            FROM
                information_schema.routines
            WHERE
                routine_schema = ? AND
                routine_type = 'FUNCTION'
            ORDER BY
                routine_name;";
        $stmt = $this->db->getStatement($query);
        $stmt->execute([self::$clientMasterDbName]);
        $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        $stmt->closeCursor();
        foreach ($rows as $row) {
            $functionCreateCommand = $this->db->pdo->query("SHOW CREATE FUNCTION `{$row['routine_name']}`")->fetchColumn(1);
            $this->newDbObj->pdo->exec($functionCreateCommand);
            $result[] = "Function `{$row['routine_name']}` created";
        }
    }

    function createSps(&$result)
    {
        $query = "SELECT
                routine_name
            FROM
                information_schema.routines
            WHERE
                routine_schema = ? AND
                routine_type = 'PROCEDURE'
            ORDER BY
                routine_name;";
        $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        $stmt->closeCursor();
        foreach ($rows as $row) {
            $functionCreateCommand = $this->db->pdo->query("SHOW CREATE PROCEDURE `{$row['routine_name']}`")->fetchColumn(1);
            $this->newDbObj->pdo->exec($functionCreateCommand);
            $result[] = "Procedure `{$row['routine_name']}` created";
        }
    }

}
