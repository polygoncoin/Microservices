<?php
namespace App\Servers\Database;

use App\Constants;
use App\Env;
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
        if (Env::$dbType === 'MySQL') {
            self::$db = new MySQL(
                Env::$dbHostname,
                Env::$dbPort,
                Env::$dbUsername,
                Env::$dbPassword,
                Env::$dbDatabase
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
