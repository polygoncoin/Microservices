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
     * @param string $dbType    Database Type
     * @param string $hostname  Hostname .env string
     * @param string $username  Username .env string
     * @param string $password  Password .env string
     * @param string $database  Database .env string
     * @return void
     */
    public static function connect(
        $dbType,
        $hostname,
        $port,
        $username,
        $password,
        $database = null
    )
    {
        self::$dbType = getenv($dbType);
        self::$hostname = $hostname;
        self::$port = $port;
        self::$username = $username;
        self::$password = $password;
        self::$database = $database;

        if(self::$dbType === 'MySQL') {
            self::$db = new MySQL(
                $hostname,
                $port,
                $username,
                $password,
                $database
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
        if (!is_null(self::$db)) {
            return self::$db;
        }

    }
}
