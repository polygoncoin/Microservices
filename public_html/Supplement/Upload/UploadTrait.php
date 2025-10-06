<?php

/**
 * UploadAPI
 * php version 8.3
 *
 * @category  UploadAPI
 * @package   Microservices
 * @author    Ramesh N Jangid <polygon.co.in@gmail.com>
 * @copyright 2025 Ramesh N Jangid
 * @license   MIT https://opensource.org/license/mit
 * @link      https://github.com/polygoncoin/Microservices
 * @since     Class available since Release 1.0.0
 */

namespace Microservices\public_html\Supplement\Upload;

/**
 * UploadAPI Trait
 * php version 8.3
 *
 * @category  UploadAPI_Trait
 * @package   Microservices
 * @author    Ramesh N Jangid <polygon.co.in@gmail.com>
 * @copyright 2025 Ramesh N Jangid
 * @license   MIT https://opensource.org/license/mit
 * @link      https://github.com/polygoncoin/Microservices
 * @since     Class available since Release 1.0.0
 */
trait UploadTrait
{
    /**
     * Saves file as stream
     *
     * @param string $absFilePath Absolute file path
     *
     * @return bool
     */
    private function saveFile($absFilePath): bool
    {
        $src = fopen(filename: "php://input", mode: "rb");
        $dest = fopen(filename: $absFilePath, mode: 'wb');

        stream_copy_to_stream(from: $src, to: $dest);

        fclose(stream: $dest);
        fclose(stream: $src);

        return true;
    }
}
