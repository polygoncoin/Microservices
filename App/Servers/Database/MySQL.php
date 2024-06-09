<?php
namespace App\Servers\Database;

use App\Constants;
use App\Env;
use App\HttpRequest;
use App\HttpResponse;
use App\Logs;
use App\Servers\Database\AbstractDatabase;

/**
 * Loading database server
 *
 * This class is built to handle MySQL database operation.
 *
 * @category   Database - MySQL
 * @package    Microservices
 * @author     Ramesh Narayan Jangid
 * @copyright  Ramesh Narayan Jangid
 * @version    Release: @1.0.0@
 * @since      Class available since Release 1.0.0
 */
class MySQL extends AbstractDatabase
{
    /**
     * Database hostname
     *
     * @var string
     */
    private $hostname = null;

    /**
     * Database port
     *
     * @var string
     */
    private $port = null;

    /**
     * Database username
     *
     * @var string
     */
    private $username = null;

    /**
     * Database password
     *
     * @var string
     */
    private $password = null;

    /**
     * Database database
     *
     * @var string
     */
    private $database = null;

    /**
     * Database connection
     *
     * @var object
     */
    private $pdo = null;

    /**
     * Executed query statement
     *
     * @var object
     */
    private $stmt = null;

    /**
     * Transaction started flag
     *
     * @var boolean
     */
    public $beganTransaction = false;

    /**
     * Database constructor
     *
     * @param string $hostname  Hostname .env string
     * @param string $username  Username .env string
     * @param string $password  Password .env string
     * @param string $database  Database .env string
     * @return void
     */
    public function __construct(
        $hostname,
        $port,
        $username,
        $password,
        $database = null
    )
    {
        $this->hostname = $hostname;
        $this->port = $port;
        $this->username = $username;
        $this->password = $password;
        if (!is_null($database)) {
            $this->database = $database;
        }
    }

    /**
     * Database connection
     *
     * @return void
     */
    public function connect()
    {
        if (!is_null($this->pdo)) return;
        try {
            $this->pdo = new \PDO(
                "mysql:host=".$this->hostname,
                $this->username,
                $this->password,
                [
                    \PDO::ATTR_EMULATE_PREPARES => false,
//                    \PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => false
                ]
            );
            if (!is_null($this->database)) {
                $this->useDatabase($this->database);
            }
        } catch (\PDOException $e) {
            HttpResponse::return5xx(501, 'Unable to connect to database server');
            return;
        }
    }

    /**
     * Use Database
     *
     * @param string $database Database .env string
     * @return void
     */
    public function useDatabase($database)
    {
        $this->connect();
        try {
            $this->pdo->exec("USE `{$database}`");
        } catch (\PDOException $e) {
            if ((int)$this->pdo->errorCode()) {
                $log = [
                    'datetime' => date('Y-m-d H:i:s'),
                    'input' => HttpRequest::$input,
                    'error' => $this->pdo->errorInfo()
                ];
                Logs::log('error', json_encode($log));
                $this->rollback();
            }
            HttpResponse::return5xx(501, 'Unable to change database');
            return;
        }
    }

    /**
     * Begin transaction
     *
     * @return void
     */
    public function begin()
    {
        $this->connect();
        $this->beganTransaction = true;
        $this->pdo->beginTransaction();
    }
    
    /**
     * Commit transaction
     *
     * @return void
     */
    public function commit()
    {
        if ($this->beganTransaction) {
            $this->beganTransaction = false;
            $this->pdo->commit();
        }
    }
    
    /**
     * Rollback transaction
     *
     * @return void
     */
    public function rollback()
    {
        if ($this->beganTransaction) {
            $this->beganTransaction = false;
            $this->pdo->rollback();
        }
    }
    
    /**
     * Affected Rows by PDO
     *
     * @return int
     */
    public function affectedRows()
    {
        if ($this->stmt) {
            return $this->stmt->rowCount();
        } else {
            return false;
        }
    }
    
    /**
     * Last Insert Id by PDO
     *
     * @return int
     */
    public function lastInsertId()
    {
        return $this->pdo->lastInsertId();
    }

    /**
     * Execute parameterised query
     *
     * @param string $sql  Parameterised query
     * @param array  $params Parameterised query params
     * @return object
     */
    public function execDbQuery($sql, $params = [])
    {
        $this->connect();
        $this->stmt = $this->pdo->prepare($sql, [\PDO::ATTR_CURSOR => \PDO::CURSOR_FWDONLY]);
        if ($this->stmt) {
            $this->stmt->execute($params);
        } else {
            if ($this->beganTransaction) {
                $this->rollback();
            }
            $log = [
                'datetime' => date('Y-m-d H:i:s'),
                'input' => HttpRequest::$input,
                'error' => $this->pdo->errorInfo()
            ];
            Logs::log('error', json_encode($log));
        }
    }

    /**
     * Prepare Sql
     *
     * @param string $sql  SQL query
     * @return object
     */
    public function prepare($sql)
    {
        $this->connect();
        $stmt = $this->pdo->prepare($sql, [\PDO::ATTR_CURSOR => \PDO::CURSOR_FWDONLY]);
        if ($stmt) {
            return $stmt;
        } else {
            if ($this->beganTransaction) {
                $this->rollback();
            }
            $log = [
                'datetime' => date('Y-m-d H:i:s'),
                'input' => HttpRequest::$input,
                'error' => $this->pdo->errorInfo()
            ];
            Logs::log('error', json_encode($log));
        }
    }

    /**
     * Fetch row from statement
     *
     * @return array
     */
    public function fetch()
    {
        if ($this->stmt) {
            return $this->stmt->fetch(\PDO::FETCH_ASSOC);
        } else {
            return false;
        }
    }

    /**
     * Fetch all rows from statement
     *
     * @return array
     */
    public function fetchAll()
    {
        if ($this->stmt) {
            return $this->stmt->fetchAll(\PDO::FETCH_ASSOC);
        } else {
            return false;
        }
    }

    /**
     * Close statement cursor
     *
     * @return void
     */
    public function closeCursor()
    {
        if ($this->stmt) {
            $this->stmt->closeCursor();
        }
    }
}
