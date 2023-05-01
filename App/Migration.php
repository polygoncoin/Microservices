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
            'globalDbName'
        );

        try {
            $query = "SELECT * from `{${getenv('connections')}}` WHERE id = ? AND is_deleted = 'No'";
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
            $dbDetails['db_password']
        );
        $this->newDb = $dbDetails['db_database'];

        $result = [];
        $this->createDatabase($result);
        $this->newDbObj->useDatabase($this->newDb);
        $tables = $this->createTables($result);
        foreach ($tables as $tableName) {
            $this->copyTableData($tableName, $result);
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
        $stmt = $this->newDbObj->getStatement("CREATE DATABASE IF NOT EXISTS `?`");
        $stmt->execute([getenv($this->newDb)]);
        $stmt->closeCursor();
        $result[] = "Database `{$this->newDb}` created";
    }
    
    function createTables(&$result)
    {
        $stmt = $this->db->getStatement("SHOW TABLES");
        $stmt->execute();
        $tables = array_column($stmt->fetchAll(\PDO::FETCH_COLUMN), 0);
        $stmt->closeCursor();
        foreach ($tables as $tableName) {
            $stmt = $this->db->getStatement("SHOW CREATE TABLE `?`");
            $stmt->execute([$tableName]);
            $command = $stmt->fetchColumn(1);
            $stmt->closeCursor();
            $stmt = $this->newDbObj->getStatement($command);
            $stmt->execute();
            $stmt->closeCursor();
            $result[] = "Table `{$tableName}` created";
        }
        return $tables;
    }

    function copyTableData($tableName, &$result)
    {
        try {
            $query = "SELECT * FROM `{$tableName}`";
            $stmt = $this->db->getStatement($query);
            $stmt->execute();
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
            $result[] = "Data for table `{$tableName}` copied";
        } catch(\PDOException $e) {
            HttpErrorResponse::return501('Database error: ' . $e->getMessage());
        }
    }

    function createViews(&$result)
    {
        $stmt = $this->db->getStatement("SHOW FULL TABLES WHERE table_type = 'VIEW'");
        $stmt->execute();
        $views = array_column($stmt->fetchAll(PDO::FETCH_NUM), 0);
        $stmt->closeCursor();
        foreach ($views as $view) {
            $stmt = $this->db->getStatement("SHOW CREATE VIEW `?`");
            $stmt->execute([$view]);
            $command = $stmt->fetchColumn(1);
            $stmt->closeCursor();
            $stmt = $this->newDbObj->getStatement($command);
            $stmt->execute();
            $stmt->closeCursor();
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
        $stmt->execute([getenv('clientMasterDbName')]);
        $functions = array_column($stmt->fetchAll(PDO::FETCH_NUM), 0);
        $stmt->closeCursor();
        foreach ($functions as $function) {
            $stmt = $this->db->getStatement("SHOW CREATE FUNCTION ?");
            $stmt->execute([$function]);
            $command = $stmt->fetchColumn(1);
            $stmt->closeCursor();
            $stmt = $this->newDbObj->getStatement($command);
            $stmt->execute();
            $stmt->closeCursor();
            $result[] = "Function `{$function}` created";
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
        $stmt = $this->db->getStatement($query);
        $stmt->execute([getenv('clientMasterDbName')]);
        $sps = array_column($stmt->fetchAll(PDO::FETCH_NUM), 0);
        $stmt->closeCursor();
        foreach ($sps as $sp) {
            $stmt = $this->db->getStatement("SHOW CREATE PROCEDURE ?");
            $stmt->execute([$sp]);
            $command = $stmt->fetchColumn(1);
            $stmt->closeCursor();
            $stmt = $this->newDbObj->getStatement($command);
            $stmt->execute();
            $stmt->closeCursor();
            $result[] = "Procedure `{$sp}` created";
        }
    }
}
