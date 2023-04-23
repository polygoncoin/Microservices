<?php
namespace App\Servers;

use App\HttpErrorResponse;

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
        $hostname = 'defaultDbHostname',
        $username = 'defaultDbUsername',
        $password = 'defaultDbPassword',
        $database = 'defaultDbDatabase'
    )
    {
        $this->hostname = $hostname;
        $this->username = $username;
        $this->password = $password;
        if (!empty($database)) {
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
                "mysql:host=".getenv($this->hostname).";dbname=".getenv($this->database),
                getenv($this->username),
                getenv($this->password),
                [
                    \PDO::ATTR_EMULATE_PREPARES => false,
                    \PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => false
                ]
            );
        } catch (\PDOException $e) {
            HttpErrorResponse::return501('Unable to connect to database server');
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
