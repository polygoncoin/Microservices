<?php
namespace App\Servers;

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
class Database
{
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
    private function connect()
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
        $this->pdo->exec("USE `{${getenv($database)}}`");
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
     * Process SQL and return statement object
     *
     * @param string $sql SQL query
     * @return object
     */
    public function getStatement($sql)
    {
        $this->connect();
        return $this->pdo->prepare($sql, [\PDO::ATTR_CURSOR => \PDO::CURSOR_FWDONLY]);
    }
}
