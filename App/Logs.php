<?php
namespace App;

use App\Constants;
use App\Env;
use App\HttpRequest;
use App\HttpResponse;

/**
 * Constants
 *
 * Contains all constants related to Microservices
 *
 * @category   Logging
 * @package    Microservices
 * @author     Ramesh Narayan Jangid
 * @copyright  Ramesh Narayan Jangid
 * @version    Release: @1.0.0@
 * @since      Class available since Release 1.0.0
 */
class Logs
{
    public static function log($logType, $logContent)
    {
        $logTypes = [
            'debug'      => Constants::$DOC_ROOT . '/Logs/debug',
            'info'       => Constants::$DOC_ROOT . '/Logs/info',
            'error'      => Constants::$DOC_ROOT . '/Logs/error',
            'notice'     => Constants::$DOC_ROOT . '/Logs/notice',
            'warning'    => Constants::$DOC_ROOT . '/Logs/warning',
            'critical'   => Constants::$DOC_ROOT . '/Logs/critical',
            'alert'      => Constants::$DOC_ROOT . '/Logs/alert',
            'emergency'  => Constants::$DOC_ROOT . '/Logs/emergency'
        ];
    
        if (!in_array($logType, array_keys($logTypes))) {
            HttpResponse::return5xx(501, 'Invalid logType');
        }
        $logFile = $logTypes[$logType];
        file_put_contents($logFile.'-'.date('Y-m'), $logContent . PHP_EOL, FILE_APPEND);
    }
}