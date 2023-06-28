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
        $serverType = null,
        $hostname = null,
        $username = null,
        $password = null,
        $database = null
    )
    {
        if (!is_null(self::$db)) {
            return self::$db;
        }

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
}