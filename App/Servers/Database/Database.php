<?php
namespace App\Servers\Database;

use App\HttpErrorResponse;
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
    public static $serverType = null;

    /**
     * Database hostname
     *
     * @var string
     */
    public static $hostname = null;

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
     */
    public static function getDbObject(
        $serverType,
        $hostname,
        $username,
        $password,
        $database = null
    )
    {
        self::$serverType = $serverType;
        self::$hostname = $hostname;
        self::$username = $username;
        self::$password = $password;
        self::$database = $database;

        if($serverType === 'MySQL') {
            self::$db = new MySQL(
                $hostname,
                $username,
                $password,
                $database
            );
        }
        return self::$db;
    }

    /**
     * Database constructor
     */
    public static function getDb()
    {
        if (!is_null(self::$db)) {
            return self::$db;
        }

    }
}