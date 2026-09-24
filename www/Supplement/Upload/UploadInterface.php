<?php

/**
 * UploadAPI
 * php version 8.3
 *
 * @category  UploadAPI
 * @package   Microservices
 * @author    Ramesh N. Jangid (Sharma) <polygon.co.in@gmail.com>
 * @copyright © 2026 Ramesh N. Jangid (Sharma)
 * @license   MIT https://opensource.org/license/mit
 * @link      https://github.com/polygoncoin/Microservices
 * @since     Class available since Release 1.0.0
 */

namespace Microservices\www\Supplement\Upload;

/**
 * UploadAPI Interface
 * php version 8.3
 *
 * @category  UploadAPI_Interface
 * @package   Microservices
 * @author    Ramesh N. Jangid (Sharma) <polygon.co.in@gmail.com>
 * @copyright © 2026 Ramesh N. Jangid (Sharma)
 * @license   MIT https://opensource.org/license/mit
 * @link      https://github.com/polygoncoin/Microservices
 * @since     Class available since Release 1.0.0
 */
interface UploadInterface
{
	/**
	 * Initialize
	 *
	 * @return bool
	 */
	public function init(): bool;

	/**
	 * Process
	 *
	 * @return mixed
	 */
	public function process(): mixed;
}
