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
    public const debug      = Constants::$DOC_ROOT . '/Logs/debug';
    public const info       = Constants::$DOC_ROOT . '/Logs/info';
    public const error      = Constants::$DOC_ROOT . '/Logs/error';
    public const notice     = Constants::$DOC_ROOT . '/Logs/notice';
    public const warning    = Constants::$DOC_ROOT . '/Logs/warning';
    public const critical   = Constants::$DOC_ROOT . '/Logs/critical';
    public const alert      = Constants::$DOC_ROOT . '/Logs/alert';
    public const emergency  = Constants::$DOC_ROOT . '/Logs/emergency';

    public static function log($logType, $logContent)
    {
        if (!in_array($logType, ['debug', 'info', 'error', 'notice', 'warning', 'critical', 'alert', 'emergency'])) {
            HttpResponse::return5xx(501, 'Invalid logType');
        }
        eval('$logFile = App\Logs::'.$logType.';');
        if (!file_exists($logFile)) {
            // create directory/folder uploads.
            mkdir($log_filename, 0644, true);
        }
        file_put_contents($logFile.'-'.date('Y-m'), $logContent . PHP_EOL, FILE_APPEND);
    }
}