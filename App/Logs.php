<?php
namespace App;

use App\Constants;
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
    public const debug      = Constants::$__DOC_ROOT__ . '/Logs/debug';
    public const info       = Constants::$__DOC_ROOT__ . '/Logs/info';
    public const error      = Constants::$__DOC_ROOT__ . '/Logs/error';
    public const notice     = Constants::$__DOC_ROOT__ . '/Logs/notice';
    public const warning    = Constants::$__DOC_ROOT__ . '/Logs/warning';
    public const critical   = Constants::$__DOC_ROOT__ . '/Logs/critical';
    public const alert      = Constants::$__DOC_ROOT__ . '/Logs/alert';
    public const emergency  = Constants::$__DOC_ROOT__ . '/Logs/emergency';

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