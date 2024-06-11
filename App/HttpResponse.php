<?php
namespace App;

use App\Constants;
use App\Env;
use App\HttpRequest;
use App\JsonEncode;
use App\Logs;

/**
 * HTTP Error Response
 *
 * This class is built to handle HTTP error response.
 *
 * @category   HttpError
 * @package    Microservices
 * @author     Ramesh Narayan Jangid
 * @copyright  Ramesh Narayan Jangid
 * @version    Release: @1.0.0@
 * @since      Class available since Release 1.0.0
 */
class HttpResponse
{
    /**
     * Current JsonEncodeObject object
     *
     * @var object
     */
    public static $httpStatus = 200;

    /**
     * Current JsonEncodeObject object
     *
     * @var object
     */
    public static $httpResponse = null;

    /**
     * Return JSON object.
     */
    public static function getJsonObject()
    {
        return JsonEncode::getObject();
    }

    /**
     * Return 2xx response
     *
     * @param string $errorCode  Error code
     * @param string $errMessage Error message in 501 response
     * @return void
     */
    public static function return2xx($errorCode, $errMessage)
    {
        self::returnResponse(
            [
                'Status' => $errorCode,
                'Message' => $errMessage
            ]
        );
    }

    /**
     * Return 3xx response
     *
     * @param string $errorCode  Error code
     * @param string $errMessage Error message in 501 response
     * @return void
     */
    public static function return3xx($errorCode, $errMessage)
    {
        self::returnResponse(
            [
                'Status' => $errorCode,
                'Message' => $errMessage
            ]
        );
    }

    /**
     * Return 4xx response
     *
     * @param string  $errorCode  Error code
     * @param string  $errMessage Error message in 404 response
     * @return void
     */
    public static function return4xx($errorCode, $errMessage)
    {
        self::returnResponse(
            [
                'Status' => $errorCode,
                'Message' => $errMessage
            ]
        );
    }

    /**
     * Return 5xx response
     *
     * @param string $errorCode  Error code
     * @param string $errMessage Error message in 501 response
     * @return void
     */
    public static function return5xx($errorCode, $errMessage)
    {
        self::returnResponse(
            [
                'Status' => $errorCode,
                'Message' => $errMessage
            ]
        );
    }

    /**
     * Return HTTP response
     *
     * @param array $arr Array containing details of HTTP response
     * @return void
     */
    public static function returnResponse($arr)
    {
        $jsonEncodeObj = JsonEncode::getObject();
        $jsonEncodeObj = null;
        self::$httpResponse = json_encode($arr);
    }

    /**
     * Check HTTP Response to proceed ahead.
     *
     * @return boolean
     */
    public static function isSuccess()
    {
        return is_null(HttpResponse::$httpResponse);
    }
}
