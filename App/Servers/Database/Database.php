<?php
namespace App\Servers\Database;

use App\HttpRequest;
use App\HttpResponse;
use App\Logs;
use App\Servers\Database\MySQL;

/**
 * Loading database class
 *
 * This class is built to handle MySQL database operation.
 *
 * @category   Database
 * @package    Microservices
 * @author     Ramesh Narayan Jangid
 * @copyright  Ramesh Narayan Jangid
 * @version    Release: @1.0.0@
 * @since      Class available since Release 1.0.0
 */
class Database
{
    /**
     * Server Type
     *
     * @var string
     */
    public static $dbType = null;

    /**
     * Database hostname
     *
     * @var string
     */
    public static $hostname = null;

    /**
     * Database port
     *
     * @var string
     */
    public static $port = null;

    /**
     * Database username
     *
     * @var string
     */
    public static $username = null;

    /**
     * Database password
     *
     * @var string
     */
    public static $password = null;

    /**
     * Database database
     *
     * @var string
     */
    public static $database = null;

    /**
     * Database object
     */
    public static $db = null;

    /**
     * Database constructor
     *
     * @return void
     */
    public static function connect()
    {
        if (getenv(self::$dbType) === 'MySQL') {
            self::$db = new MySQL(
                self::$hostname,
                self::$port,
                self::$username,
                self::$password,
                self::$database
            );
        }
    }

    /**
     * Database constructor
     *
     * @return object
     */
    public static function getObject()
    {
        if (is_null(self::$db)) {
            self::connect();
        }
        return self::$db;
    }
}
