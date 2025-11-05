<?php

/**
 * Write APIs
 * php version 8.3
 *
 * @category  Counter
 * @package   Microservices
 * @author    Ramesh N Jangid <polygon.co.in@gmail.com>
 * @copyright 2025 Ramesh N Jangid
 * @license   MIT https://opensource.org/license/mit
 * @link      https://github.com/polygoncoin/Microservices
 * @since     Class available since Release 1.0.0
 */

namespace Microservices\App;

use Microservices\App\Common;
use Microservices\App\DbFunctions;
use Microservices\App\Env;

/**
 * Write APIs
 * php version 8.3
 *
 * @category  Counter
 * @package   Microservices
 * @author    Ramesh N Jangid <polygon.co.in@gmail.com>
 * @copyright 2025 Ramesh N Jangid
 * @license   MIT https://opensource.org/license/mit
 * @link      https://github.com/polygoncoin/Microservices
 * @since     Class available since Release 1.0.0
 */
class Counter
{
    public static $globalDbObj = null;

    /**
     * Get Global Auto Increment Counter
     *
     * @return int
     */
    public static function getGlobalCounter(): int
    {
        if (self::$globalDbObj === null) {
            $globalDbType = getenv(name: 'globalDbType');
            $globalDbHostname = getenv(name: 'globalDbHostname');
            $globalDbPort = getenv(name: 'globalDbPort');
            $globalDbUsername = getenv(name: 'globalDbUsername');
            $globalDbPassword = getenv(name: 'globalDbPassword');
            $globalDbDatabase = getenv(name: 'globalDbDatabase');

            self::$globalDbObj = Common::$req->connectDb(
                dbType: $globalDbType,
                dbHostname: $globalDbHostname,
                dbPort: $globalDbPort,
                dbUsername: $globalDbUsername,
                dbPassword: $globalDbPassword,
                dbDatabase: $globalDbDatabase
            );
        }

        $table = Env::$globalDbDatabase . '.' . Env::$counter;
        $sql = "INSERT INTO {$table}() VALUES()";
        $sqlParams = [];
        
        self::$globalDbObj->execDbQuery(sql: $sql, params: $sqlParams);
        $id = self::$globalDbObj->lastInsertId();
        
        return $id;
    }
}
