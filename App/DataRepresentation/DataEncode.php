<?php

/**
 * Creates Data Representation Output
 * php version 8.3
 *
 * @category  DataEncode
 * @package   Microservices
 * @author    Ramesh N. Jangid (Sharma) <polygon.co.in@gmail.com>
 * @copyright © 2026 Ramesh N. Jangid (Sharma)
 * @license   MIT https://opensource.org/license/mit
 * @link      https://github.com/polygoncoin/Microservices
 * @since     Class available since Release 1.0.0
 */

namespace Microservices\App\DataRepresentation;

use Microservices\App\Constant;
use Microservices\App\DataRepresentation\Encode\PhpEncode;
use Microservices\App\DataRepresentation\Encode\JsonEncode;
use Microservices\App\DataRepresentation\Encode\XmlEncode;
use Microservices\App\Http;

/**
 * Creates Data Representation Output
 * php version 8.3
 *
 * @category  DataEncoder
 * @package   Microservices
 * @author    Ramesh N. Jangid (Sharma) <polygon.co.in@gmail.com>
 * @copyright © 2026 Ramesh N. Jangid (Sharma)
 * @license   MIT https://opensource.org/license/mit
 * @link      https://github.com/polygoncoin/Microservices
 * @since     Class available since Release 1.0.0
 */
class DataEncode
{
	/**
	 * Temporary Stream
	 *
	 * @var null|resource|array
	 */
	private $tempStream = null;

	/**
	 * HTTP object
	 *
	 * @var null|Http
	 */
	private $httpObj = null;

	/**
	 * Temporary Stream
	 *
	 * @var null|Object
	 */
	private $dataEncoderObj = null;

	/**
	 * XSLT file
	 *
	 * @var null|string
	 */
	public $xsltFile = null;

	/**
	 * HTML file
	 *
	 * @var null|string
	 */
	public $htmlFile = null;

	/**
	 * PHP file
	 *
	 * @var null|string
	 */
	public $phpFile = null;

	/**
	 * Constructor
	 *
	 * @param Http $httpObj
	 */
	public function __construct(
		Http &$httpObj
	) {
		$this->httpObj = &$httpObj;
	}

	/**
	 * Initialize
	 *
	 * @param bool $header Append XML header flag
	 *
	 * @return void
	 */
	public function init(
		$header = true
	): void {
		if ($this->httpObj->httpReqData['server']['httpMethod'] === Constant::$GET) {
			if ($this->httpObj->responseObj->outputRepresentation === 'PHP') {
				$this->tempStream = [];
			} else {
				$this->tempStream = fopen(
					filename: "php://temp",
					mode: "rw+b"
				);
			}
		} else {
			if ($this->httpObj->responseObj->outputRepresentation === 'PHP') {
				$this->tempStream = [];
			} else {
				$this->tempStream = fopen(
					filename: "php://memory",
					mode: "rw+b"
				);
			}
		}
		switch ($this->httpObj->responseObj->outputRepresentation) {
			case 'JSON':
				$this->dataEncoderObj = new JsonEncode(
					tempStream: $this->tempStream,
					header: $header
				);
				break;
			case 'PHP':
				$this->dataEncoderObj = new PhpEncode(
					tempStream: $this->tempStream,
					header: $header
				);
				break;
			case 'XML':
			case 'XSLT':
			case 'HTML':
				$this->dataEncoderObj = new XmlEncode(
					tempStream: $this->tempStream,
					header: $header
				);
				break;
			default:
				break;
		}
	}

	/**
	 * Start array
	 *
	 * @param null|string $objectKey Used while creating simple array inside an object
	 *
	 * @return void
	 */
	public function startArray(
		$objectKey = null
	): void {
		$this->dataEncoderObj->startArray(
			objectKey: $objectKey
		);
	}

	/**
	 * Add array/value as in the data format
	 *
	 * @param string|array $data Representation Data
	 *
	 * @return void
	 * @throws \Exception
	 */
	public function addArrayData(
		$data
	): void {
		$this->dataEncoderObj->addArrayData(
			data: $data
		);
	}

	/**
	 * End array
	 *
	 * @return void
	 */
	public function endArray(): void
	{
		$this->dataEncoderObj->endArray();
	}

	/**
	 * Start object
	 *
	 * @param null|string $objectKey Used while creating associative array inside an object
	 *
	 * @return void
	 * @throws \Exception
	 */
	public function startObject(
		$objectKey = null
	): void {
		$this->dataEncoderObj->startObject(
			objectKey: $objectKey
		);
	}

	/**
	 * Add array/value as in the data format
	 *
	 * @param string       $objectKey Key of associative array
	 * @param string|array $data      Representation Data
	 *
	 * @return void
	 * @throws \Exception
	 */
	public function addKeyData(
		$objectKey,
		$data
	): void {
		$this->dataEncoderObj->addKeyData(
			objectKey: $objectKey,
			data: $data
		);
	}

	/**
	 * End object
	 *
	 * @return void
	 */
	public function endObject(): void
	{
		$this->dataEncoderObj->endObject();
	}

	/**
	 * Encode data
	 *
	 * @param string|array $data Representation Data
	 *
	 * @return void
	 */
	public function encode(
		$data
	): void {
		$this->dataEncoderObj->encode(
			data: $data
		);
	}

	/**
	 * Append raw data string
	 *
	 * @param string $data Representation Data
	 *
	 * @return void
	 */
	public function appendData(&$data): void
	{
		$this->dataEncoderObj->appendData(
			data: $data
		);
	}

	/**
	 * Append object data
	 *
	 * @param string $objectKey Key of associative array
	 * @param string $data      Representation Data
	 *
	 * @return void
	 */
	public function appendKeyData(
		$objectKey,
		&$data
	): void {
		$this->dataEncoderObj->appendKeyData(
			objectKey: $objectKey,
			data: $data
		);
	}

	/**
	 * End encoding
	 *
	 * @return void
	 */
	public function end(): void
	{
		$this->dataEncoderObj->end();
	}

	/**
	 * Stream encoded data
	 *
	 * @return void
	 */
	public function streamData(): void
	{
		$this->end();

		switch (true) {
			case (
					$this->httpObj->responseObj->outputRepresentation === 'XSLT'
					&& $this->xsltFile !== null
					&& file_exists(
						filename: $this->xsltFile
					)
				):
				echo $this->processPublicXml(
					xmlFile: $this->xsltFile
				);
				fclose(
					stream: $this->tempStream
				);
				break;
			case (
					$this->httpObj->responseObj->outputRepresentation === 'HTML'
					&& $this->htmlFile !== null
					&& file_exists(
						filename: $this->htmlFile
					)
				):
				echo $this->processPublicXml(
					xmlFile: $this->htmlFile
				);
				fclose(
					stream: $this->tempStream
				);
				break;
			case (
					$this->httpObj->responseObj->outputRepresentation === 'PHP'
					&& $this->phpFile !== null
					&& file_exists(
						filename: $this->phpFile
					)
				):
				$finalArray = &$this->tempStream->finalArray;
				include_once $this->phpFile;
				$this->tempStream = null;
				break;
			default:
				rewind(
					stream: $this->tempStream
				);
				$outputStream = fopen(
					filename: 'php://output',
					mode: 'wb'
				);
				stream_copy_to_stream(
					from: $this->tempStream,
					to: $outputStream
				);
				fclose(
					stream: $outputStream
				);
				fclose(
					stream: $this->tempStream
				);
				break;
		}
	}

	/**
	 * Get encoded data
	 *
	 * @return bool|string
	 */
	public function getData(): bool|string
	{
		$this->end();

		switch (true) {
			case (
					$this->httpObj->responseObj->outputRepresentation === 'XSLT'
					&& $this->xsltFile !== null
					&& file_exists(
						filename: $this->xsltFile
					)
				):
				$streamContent = $this->processPublicXml(
					xmlFile: $this->xsltFile
				);
				fclose(
					stream: $this->tempStream
				);
				break;
			case (
					$this->httpObj->responseObj->outputRepresentation === 'HTML'
					&& $this->htmlFile !== null
					&& file_exists(
						filename: $this->htmlFile
					)
				):
				$streamContent = $this->processPublicXml(
					xmlFile: $this->htmlFile
				);
				fclose(
					stream: $this->tempStream
				);
				break;
			case (
					$this->httpObj->responseObj->outputRepresentation === 'PHP'
					&& $this->phpFile !== null
					&& file_exists(
						filename: $this->phpFile
					)
				):
				$finalArray = &$this->dataEncoderObj->finalArray;
				@ob_clean();
				include_once $this->phpFile;
				$streamContent = ob_get_clean();
				$this->tempStream = null;
				break;
			default:
				rewind(
					stream: $this->tempStream
				);
				$streamContent = stream_get_contents(
					stream: $this->tempStream
				);
				fclose(
					stream: $this->tempStream
				);
				break;
		}

		return $streamContent;
	}

	/**
	 * Generate XML(XSLT)/HTML data
	 *
	 * @param string $xmlFile XML file location
	 *
	 * @return string
	 */
	private function processPublicXml(
		$xmlFile
	): string {
		rewind(
			stream: $this->tempStream
		);
		$xml = new \DOMDocument();
		$xml->loadXML(
			source: stream_get_contents(
				stream: $this->tempStream
			)
		);

		$xslt = new \XSLTProcessor();
		$XSL = new \DOMDocument();
		$XSL->load(
			filename: $this->xmlFile
		);
		$xslt->importStylesheet(
			stylesheet: $XSL
		);
		return $xslt->transformToXML(
			document: $xml
		);
	}
}
