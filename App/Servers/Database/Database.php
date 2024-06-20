<?php
namespace Microservices\App\Servers\Database;

use Microservices\App\Constants;
use Microservices\App\Env;
use Microservices\App\Servers\Database\MySQL;

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
    static public $db = null;

    /**
     * Database constructor
     *
     * @return void
     */
    static public function connect()
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
    static public function getObject()
    {
        if (is_null(self::$db)) {
            self::connect();
        }
        return self::$db;
    }
}
