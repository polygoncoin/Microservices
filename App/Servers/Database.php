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
    function __construct($hostname, $username, $password, $database = null)
    {
        $this->hostname = getenv($hostname);
        $this->username = getenv($username);
        $this->password = getenv($password);
        if (!empty($database)) {
            $this->database = getenv($database);
        }
    }

    /**
     * Database connection
     *
     * @return void
     */
    function connect()
    {
        if (!is_null($this->pdo)) return;
        try {
            $this->pdo = new \PDO(
                "mysql:host={$this->hostname};dbname={$this->database}",
                $this->username,
                $this->password,
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
     * Prepare select SQL and return statement object
     *
     * @param string $sql SQL statement
     * @return object
     */
    public function select($sql)
    {
        $this->connect();
        return $this->pdo->prepare($sql, [PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY]);
    }

    /**
     * Prepare insert SQL and return statement object
     *
     * @param string $sql SQL statement
     * @return object
     */
    public function insert($sql)
    {
        $this->connect();
        return $this->pdo->prepare($sql, [PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY]);
    }

    /**
     * Prepare update SQL and return statement object
     *
     * @param string $sql SQL statement
     * @return object
     */
    public function update($sql)
    {
        $this->connect();
        return $this->pdo->prepare($sql, [PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY]);
    }
}
