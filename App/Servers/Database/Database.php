<?php
namespace App\Servers\Database;

use App\Servers\Database\MySQL;
use App\HttpErrorResponse;

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
     */
    public static function getDbObject(
        $serverType,
        $hostname,
        $username,
        $password,
        $database = null
    )
    {
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