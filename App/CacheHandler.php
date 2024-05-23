<?php
namespace App;

use App\HttpRequest;
use App\HttpResponse;
use App\Logs;

/**
 * Class to reduce cache hits and save on bandwidth using ETags and cache headers.
 *
 * HTTP etags header helps reduce the cache hits
 * Helps browser avoid unwanted hits to un-modified content on the server
 * which are cached on client browser.
 * The headers in class helps fetch only the modified content.
 * 
 * @category   PHP File Cache handler
 * @package    Microservices
 * @author     Ramesh Narayan Jangid
 * @copyright  Ramesh Narayan Jangid
 * @version    Release: @1.0.0@
 * @since      Class available since Release 1.0.0
 */
class CacheHandler
{
    /**
     * File Path
     * 
     * @var array
     */
    private static $filePath;

    /**
     * Cache Folder
     * 
     * The folder location outside docroot
     * without a slash at the end
     * 
     * @var string
     */
    private static $cacheLocation = __DOCROOT__ . '/Dropbox';

    /**
     * Initalise check and serve file
     *
     * @return void
     */
    public static function init()
    {
        self::$filePath = str_replace('/cache', '', ROUTE);
        self::validateFileRequest();
        
        $filePath = self::$route;
        
        $fileLocation = self::$cacheLocation . $filePath;
        $modifiedTime = filemtime($fileLocation);

        // Let Etag be last modified timestamp of file.
        $eTag = "{$modifiedTime}";

        if (
            (
                isset($_SERVER['HTTP_IF_NONE_MATCH']) &&
                strpos($_SERVER['HTTP_IF_NONE_MATCH'], $eTag) !== false
            ) ||
            (
                isset($_SERVER['HTTP_IF_MODIFIED_SINCE']) &&
                @strtotime($_SERVER['HTTP_IF_MODIFIED_SINCE']) == $modifiedTime
            )
        ) { 
            header('HTTP/1.1 304 Not Modified');
            exit;
        } else {
            self::serveFile($fileLocation, $modifiedTime, $eTag);
        }
    }

    /**
     * Checks whether access to file is allowed.
     *
     * @return void
     */
    public static function validateFileRequest()
    {
        // check logic for user is allowed to access the file as per HttpRequest::$input
        // self::$filePath;
    }

    /**
     * Serve File content
     *
     * @param string  $fileLocation
     * @param integer $modifiedTime
     * @param string  $eTag
     * @return void
     */
    private static function serveFile($fileLocation, $modifiedTime, $eTag) {
        // File name requested for download
        $fileName = basename($fileLocation);

        // Get the $fileLocation file mime
        $fileInfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = finfo_file($fileInfo, $fileLocation);
        finfo_close($fileInfo);

        // send the headers
        //header("Content-Disposition: attachment;filename='$fileName';");
        header('Cache-Control: max-age=0, must-revalidate');
        header("Last-Modified: ".gmdate("D, d M Y H:i:s", $modifiedTime)." GMT");
        header("Etag:\"{$eTag}\"");
        header('Expires: -1');
        header("Content-Type: $mime");
        header('Content-Length: ' . filesize($fileLocation));

        // Send file content as stream
        $fp = fopen($fileLocation, 'rb');
        fpassthru($fp);
        exit;
    }
}
