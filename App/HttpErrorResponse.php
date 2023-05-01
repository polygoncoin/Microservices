<?php
namespace App;

use App\JsonEncode;
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
class HttpErrorResponse
{
    /**
     * Return 2xx response
     *
     * @param string $errorCode  Error code
     * @param string $errMessage Error message in 501 response
     * @return void
     */
    public static function return2xx($errorCode, $errMessage)
    {
        self::returnHttpStatus(
            $errorCode,
            ['message' => $errMessage]
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
        self::returnHttpStatus(
            $errorCode,
            ['message' => $errMessage]
        );
    }

    /**
     * Return 4xx response
     *
     * @param string  $errorCode  Error code
     * @param string  $errMessage Error message in 404 response
     * @param boolean $customise  Customise message on basis of parameter validation
     * @return void
     */
    public static function return4xx($errorCode, $errMessage, $customise = false)
    {
        if ($customise) {
            $arr = explode('|', $errMessage);
            self::returnHttpStatus(
                $errorCode,
                [
                    'Error' => [
                        'Parameter' => $arr[0],
                        'Supported Values' => explode(',', $arr[1])
                    ]
                ]
            );
        } else {
            self::returnHttpStatus(
                $errorCode,
                ['message' => $errMessage]
            );
        }
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
        self::returnHttpStatus(
            $errorCode,
            ['message' => $errMessage]
        );
    }

    /**
     * Returns HTTP response
     *
     * @param int   $statusCode Status code of HTTP response
     * @param array $arr        Array containing details for HTTP Response
     * @return void
     */
    public static function returnHttpStatus($statusCode, $arr)
    {
        self::returnResponse(
            array_merge(
                ['status' => $statusCode],
                $arr
            )
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
        $jsonEncode = new JsonEncode();
        $jsonEncode->encode($arr);
        $jsonEncode = null;
    }
}
