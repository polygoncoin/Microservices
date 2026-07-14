<?php

/**
 * Creates Data Representation Input
 * php version 8.3
 *
 * @category  DataDecode
 * @package   Microservices
 * @author    Ramesh N. Jangid (Sharma) <polygon.co.in@gmail.com>
 * @copyright © 2026 Ramesh N. Jangid (Sharma)
 * @license   MIT https://opensource.org/license/mit
 * @link      https://github.com/polygoncoin/Microservices
 * @since     Class available since Release 1.0.0
 */

namespace Microservices\App\DataRepresentation;

use Microservices\App\DataRepresentation\Decode\JsonDecode;
use Microservices\App\DataRepresentation\Decode\XmlDecode;

/**
 * Creates Data Representation Output
 * php version 8.3
 *
 * @category  DataDecoder
 * @package   Microservices
 * @author    Ramesh N. Jangid (Sharma) <polygon.co.in@gmail.com>
 * @copyright © 2026 Ramesh N. Jangid (Sharma)
 * @license   MIT https://opensource.org/license/mit
 * @link      https://github.com/polygoncoin/Microservices
 * @since     Class available since Release 1.0.0
 */
class DataDecode
{
	/**
	 * JSON File Handle
	 *
	 * @var null|resource
	 */
	private $dataFileHandle = null;

	/**
	 * Temporary Stream
	 *
	 * @var null|Object
	 */
	private $dataDecoderObj = null;

	/**
	 * Constructor
	 *
	 * @param string   $inputRepresentation Input Representation
	 * @param resource $dataFileHandle  File handle
	 */
	public function __construct(
		$inputRepresentation,
		&$dataFileHandle
	) {
		$this->dataFileHandle = &$dataFileHandle;

		if ($inputRepresentation === 'JSON') {
			$this->dataDecoderObj = new JsonDecode(
				jsonFileHandle: $this->dataFileHandle
			);
		} else {
			$this->dataDecoderObj = new XmlDecode(
				jsonFileHandle: $this->dataFileHandle
			);
		}
	}

	/**
	 * Initialize
	 *
	 * @return bool
	 */
	public function init(): bool
	{
		return $this->dataDecoderObj->init();
	}

	/**
	 * Validates data
	 *
	 * @return void
	 */
	public function validate(): void
	{
		$this->dataDecoderObj->validate();
	}

	/**
	 * Index data
	 *
	 * @return void
	 */
	public function indexData(): void
	{
		$this->dataDecoderObj->indexData();
	}

	/**
	 * Result exist as per $keyString
	 *
	 * @param null|string $keyString Key's exist (values separated by colon)
	 *
	 * @return bool
	 */
	public function isset(
		$keyString = null
	): bool {
		return $this->dataDecoderObj->isset(
			keyString: $keyString
		);
	}

	/**
	 * Datatype of result as per $keyString
	 *
	 * @param null|string $keyString Key's exist (values separated by colon)
	 *
	 * @return string Object/Array
	 */
	public function dataType(
		$keyString = null
	): string {
		return $this->dataDecoderObj->dataType(
			keyString: $keyString
		);
	}

	/**
	 * Count of result as per $keyString
	 *
	 * @param null|string $keyString Key values separated by colon
	 *
	 * @return int
	 */
	public function count(
		$keyString = null
	): int {
		return $this->dataDecoderObj->count(
			keyString: $keyString
		);
	}

	/**
	 * Get result as per $keyString
	 *
	 * @param string $keyString Key values separated by colon
	 *
	 * @return mixed
	 */
	public function get(
		$keyString = ''
	): mixed {
		return $this->dataDecoderObj->get(
			keyString: $keyString
		);
	}

	/**
	 * Get complete result as per $keyString
	 *
	 * @param string $keyString Key values separated by colon
	 *
	 * @return mixed
	 */
	public function getCompleteArray(
		$keyString = ''
	): mixed {
		return $this->dataDecoderObj->getCompleteArray(
			keyString: $keyString
		);
	}

	/**
	 * Load result as per $keyString
	 * Start processing the JSON string for a key's
	 * Perform search inside key's of JSON like $json['data'][0]['data1']
	 *
	 * @param string $keyString Key values separated by colon
	 *
	 * @return void
	 * @throws \Exception
	 */
	public function load(
		$keyString
	): void {
		$this->dataDecoderObj->load(
			keyString: $keyString
		);
	}
}
