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
    public static $common = null;
    public static $db = null;

    /**
     * Initialize
     *
     * @param Common $common Common object
     *
     * @return int
     */
    public static function getCounter(Common &$common): int
    {
        self::$common = &$common;
        self::$db = self::$common->req->setDbConnection(fetchFrom: 'Master');

        $table = Env::$globalDbDatabase . '.' . Env::$counter;
        $sql = "INSERT INTO {$table}() VALUES()";
        $sqlParams = [];
        
        self::$db->execDbQuery(sql: $sql, params: $sqlParams);
        $id = self::$db->lastInsertId();
        
        return $id;
    }
}
