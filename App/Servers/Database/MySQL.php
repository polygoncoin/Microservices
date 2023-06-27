<?php
namespace App\Servers\Database;

use App\Servers\Database\AbstractDatabase;
use App\HttpErrorResponse;
use App\PHPTrait;

/**
 * Loading database server
 *
 * This class is built to handle loading the database server.
 *
 * @category   Cache
 * @package    Microservices
 * @author     Ramesh Narayan Jangid
 * @copyright  Ramesh Narayan Jangid
 * @version    Release: @1.0.0@
 * @since      Class available since Release 1.0.0
 */
class MySQL extends AbstractDatabase
{
    use PHPTrait;

    /**
     * Database hostname
     *
     * @var string
     */
    private $hostname = null;

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
     * Database constructor
     */
    public function __construct(
        $hostname,
        $username,
        $password,
        $database = null
    )
    {
        $this->hostname = $hostname;
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
                "mysql:host=".getenv($this->hostname),
                getenv($this->username),
                getenv($this->password),
                [
                    \PDO::ATTR_EMULATE_PREPARES => false,
                    \PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => false
                ]
            );
            if (!is_null($this->database)) {
                $this->useDatabase($this->database);
            }
        } catch (\PDOException $e) {
            echo $e->getMessage();die;
            HttpErrorResponse::return5xx(501, 'Unable to connect to database server');
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
        $this->pdo->exec("USE `{$this->execPhpFunc(getenv($database))}`");
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
     * @param string $query  Parameterised query
     * @param array  $params Parameterised query params
     * @return object
     */
    public function execDbQuery($query, $params = [])
    {
        $this->connect();
        try {
            $this->stmt = $this->pdo->prepare($query, [\PDO::ATTR_CURSOR => \PDO::CURSOR_FWDONLY]);
            $this->stmt->execute($params);
        } catch(\PDOException $e) {
            HttpErrorResponse::return5xx(501, 'Database error: ' . $e->getMessage());
        }
    }

    /**
     * Fetch row from statement
     *
     * @return array
     */
    public function fetch()
    {
        return $this->stmt->fetch(\PDO::FETCH_ASSOC);
    }

    /**
     * Fetch all rows from statement
     *
     * @return array
     */
    public function fetchAll()
    {
        return $this->stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Close statement cursor
     *
     * @return void
     */
    public function closeCursor()
    {
        $this->stmt->closeCursor();
    }
}
