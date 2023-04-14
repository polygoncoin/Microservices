<?php
/*
MIT License 

Copyright (c) 2023 Ramesh Narayan Jangid. 

Permission is hereby granted, free of charge, to any person obtaining a copy 
of this software and associated documentation files (the "Software"), to deal 
in the Software without restriction, including without limitation the rights 
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell 
copies of the Software, and to permit persons to whom the Software is 
furnished to do so, subject to the following conditions: 

The above copyright notice and this permission notice shall be included in all 
copies or substantial portions of the Software. 

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR 
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, 
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE 
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER 
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, 
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE 
SOFTWARE. 
*/
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
     * Return 404 response
     *
     * @param string  $errMessage Error message in 404 response
     * @param boolean $customise  Customise message on basis of parameter validation
     * @return void
     */
    public static function return404($errMessage, $customise = false)
    {
        if ($customise) {
            $arr = explode('|', $errMessage);
            self::returnHttpStatus(
                404,
                [
                    'Error' => [
                        'Parameter' => $arr[0],
                        'Supported Values' => explode(',', $arr[1])
                    ]
                ]
            );
        } else {
            self::returnHttpStatus(
                404,
                ['message' => $errMessage]
            );
        }
    }

    /**
     * Return 501 response
     *
     * @param string $errMessage Error message in 501 response
     * @return void
     */
    public static function return501($errMessage)
    {
        self::returnHttpStatus(
            501,
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
    private function returnHttpStatus($statusCode, &$arr)
    {
        $this->returnResponse(
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
    private function returnResponse(&$arr)
    {
        die(json_encode($arr));
    }
}
