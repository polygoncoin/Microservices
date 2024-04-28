<?php
namespace App;

use App\HttpRequest;
use App\HttpResponse;

/**
 * Trait for PHP functions
 *
 * This trait constains only one function so that one can execute inbuilt PHP
 * functions in strings enclosed with double quotes
 *
 * @category   PHP Trait
 * @package    Microservices
 * @author     Ramesh Narayan Jangid
 * @copyright  Ramesh Narayan Jangid
 * @version    Release: @1.0.0@
 * @since      Class available since Release 1.0.0
 */
trait AppTrait
{
    /**
     * Function to help execute PHP functions enclosed with double quotes.
     *
     * @param $param Returned values by PHP inbuilt functions.
     */
    function execPhpFunc($param) { return $param;}

    /**
     * Returns Query and Params for execution.
     *
     * @param array $sqlDetails Config from file
     * @return array
     */
    private function getSqlAndParams(&$sqlDetails)
    {
        $sql = $sqlDetails['query'];
        $sqlParams = [];
        if (isset($sqlDetails['payload'])) {
            if (count($sqlDetails['payload']) === 0) {
                HttpResponse::return5xx(501, 'Invalid config: Missing payload configuration');
            } else {
                if (strpos($sql, '__SET__') !== false) {
                    $sqlParams = $this->getSqlParams($sqlDetails['payload']);
                    if (!empty($sqlParams)) {
                        $__SET__ = implode(', ',array_map(function ($v) { $v = str_replace(['`', ' '], '', $v); return "`{$v}` = :{$v}";}, array_keys($sqlParams)));
                        $sql = str_replace('__SET__', $__SET__, $sql);
                    }
                } else {
                    HttpResponse::return5xx(501, 'Invalid query: Missing __SET__');
                }
            }
        }
        if (isset($sqlDetails['where'])) {
            if (count($sqlDetails['where']) === 0) {
                HttpResponse::return5xx(501, 'Invalid config: Missing where configuration');
            } else {
                if (strpos($sql, '__WHERE__') !== false) {
                    $sqlWhereParams = $this->getSqlParams($sqlDetails['where']);
                    if (!empty($sqlWhereParams)) {
                        $__WHERE__ = implode(' AND ',array_map(function ($v) { $v = str_replace(['`', ' '], '', $v); return "`{$v}` = :{$v}";}, array_keys($sqlWhereParams)));
                        $sql = str_replace('__WHERE__', $__WHERE__, $sql);
                        $sqlParams = array_merge($sqlParams, $sqlWhereParams);
                    }
                } else {
                    HttpResponse::return5xx(501, 'Invalid query: Missing __WHERE__');
                }
            }
        }
        return [$sql, $sqlParams];
    }

    /**
     * Generates Params for statement to execute.
     *
     * @param array $sqlPayload Config from file
     * @return array
     */
    private function getSqlParams(&$sqlPayload)
    {
        $sqlParams = [];
        foreach ($sqlPayload as $var => [$type, $typeKey]) {
            if ($type === 'function') {
                $sqlParams[$var] = $typeKey();
            } else if ($type === 'custom') {
                $sqlParams[$var] = $typeKey;
            } else if ($type === 'payload' && !in_array($typeKey, HttpRequest::$input['required']) && !isset(HttpRequest::$input[$type][$typeKey])) {
                continue;
            } else {
                if (!isset(HttpRequest::$input[$type][$typeKey])) {
                    HttpResponse::return5xx(501, "Invalid configuration of '{$type}' for '{$typeKey}'");
                }
                $sqlParams[$var] = HttpRequest::$input[$type][$typeKey];
            }
        }
        return $sqlParams;
    }

    /**
     * Function to find wether privider array is associative/simple array
     *
     * @param array $arr Array to search for associative/simple array
     * @return boolean
     */
    private function isAssoc($arr)
    {
        $assoc = false;
        $i = 0;
        foreach ($arr as $k => &$v) {
            if ($k !== $i++) {
                $assoc = true;
                break;
            }
        }
        return $assoc;
    }
}