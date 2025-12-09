<?php

/**
 * File Upload
 * php version 8.3
 *
 * @category  FileUpload
 * @package   Microservices
 * @author    Ramesh N Jangid <polygon.co.in@gmail.com>
 * @copyright 2025 Ramesh N Jangid
 * @license   MIT https://opensource.org/license/mit
 * @link      https://github.com/polygoncoin/Microservices
 * @since     Class available since Release 1.0.0
 */

namespace Microservices\App;

use Microservices\App\Common;

/**
 * File Upload
 * php version 8.3
 *
 * @category  FileUpload
 * @package   Microservices
 * @author    Ramesh N Jangid <polygon.co.in@gmail.com>
 * @copyright 2025 Ramesh N Jangid
 * @license   MIT https://opensource.org/license/mit
 * @link      https://github.com/polygoncoin/Microservices
 * @since     Class available since Release 1.0.0
 */
class FileUpload
{
    /**
     * Initialize
     *
     * @return bool
     */
    public static function init(): bool
    {
        self::validateFileUpload();
        if ($this->api->req->rParser->sqlConfigFile !== false) {
            return false;
        }
        return true;
    }

    /**
     * Check Errors related to File Upload
     *
     * @return void
     * @throws \Exception
     */
    public static function validateFileUpload(): void
    {
        if (count($this->api->http['files']) > 1) {
            throw new \Exception(
                message: 'Supports only one file with each request',
                code: HttpStatus::$BadRequest
            );
        }

        foreach ($this->api->http['files'] as $file => $details) {
            if (isset($details['error'])) {
                switch ($details['error']) {
                    case UPLOAD_ERR_INI_SIZE: // value 1
                        throw new \Exception(
                            message: 'Size of the uploaded file exceeds the maximum value specified',
                            code: HttpStatus::$InternalServerError
                        );
                        break;

                    case UPLOAD_ERR_FORM_SIZE: // value 2
                        throw new \Exception(
                            message: 'Size of the uploaded file exceeds the maximum value specified in the HTML form in the MAX_FILE_SIZE element',
                            code: HttpStatus::$BadRequest
                        );
                        break;

                    case UPLOAD_ERR_PARTIAL: // value 3
                        throw new \Exception(
                            message: 'The file was only partially uploaded',
                            code: HttpStatus::$InternalServerError
                        );
                        break;

                    case UPLOAD_ERR_NO_FILE: // value 4
                        throw new \Exception(
                            message: 'No file was uploaded',
                            code: HttpStatus::$InternalServerError
                        );
                        break;

                    case UPLOAD_ERR_NO_TMP_DIR: // value 6
                        throw new \Exception(
                            message: 'No temporary directory is specified',
                            code: HttpStatus::$InternalServerError
                        );
                        break;

                    case UPLOAD_ERR_CANT_WRITE: // value 7
                        throw new \Exception(
                            message: 'Writing the file to disk failed',
                            code: HttpStatus::$InternalServerError
                        );
                        break;

                    case UPLOAD_ERR_EXTENSION: // value 8
                        throw new \Exception(
                            message: 'An extension stopped the file upload process',
                            code: HttpStatus::$InternalServerError
                        );
                        break;
                }
            }
        }
    }
}
