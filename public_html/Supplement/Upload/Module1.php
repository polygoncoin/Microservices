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

use Microservices\App\Constants;
use Microservices\App\Common;
use Microservices\App\DbFunctions;
use Microservices\public_html\Supplement\Upload\UploadInterface;
use Microservices\public_html\Supplement\Upload\UploadTrait;

/**
 * UploadAPI Example
 * php version 8.3
 *
 * @category  UploadAPI_Example
 * @package   Microservices
 * @author    Ramesh N Jangid <polygon.co.in@gmail.com>
 * @copyright 2025 Ramesh N Jangid
 * @license   MIT https://opensource.org/license/mit
 * @link      https://github.com/polygoncoin/Microservices
 * @since     Class available since Release 1.0.0
 */
class Module1 implements UploadInterface
{
    use UploadTrait;

    /**
     * Constructor
     */
    public function __construct()
    {
        DbFunctions::setDbConnection(fetchFrom: 'Master');
    }

    /**
     * Initialize
     *
     * @return bool
     */
    public function init(): bool
    {
        return true;
    }

    /**
     * Process
     *
     * @param array $payload Payload
     *
     * @return array
     */
    public function process(array $payload = []): array
    {
        $absFilePath = $this->getLocation();
        $this->saveFile(absFilePath: $absFilePath);

        return [true];
    }

    /**
     * Function to get filename with location depending upon $sess
     *
     * @return string
     */
    private function getLocation(): string
    {
        return Constants::$DROP_BOX_DIR .
            DIRECTORY_SEPARATOR . 'test.png';
    }
}
