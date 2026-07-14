<?php

/**
 * Service
 * php version 8.3
 *
 * @category  Microservices
 * @package   Microservices
 * @author    Ramesh N. Jangid (Sharma) <polygon.co.in@gmail.com>
 * @copyright © 2026 Ramesh N. Jangid (Sharma)
 * @license   MIT https://opensource.org/license/mit
 * @link      https://github.com/polygoncoin/Microservices
 * @since     Class available since Release 1.0.0
 */

namespace Microservices\App;

use Microservices\App\Constant;
use Microservices\App\Dropbox;
use Microservices\App\Env;
use Microservices\App\Gateway;
use Microservices\App\Http;

/**
 * Service
 * php version 8.3
 *
 * @category  Microservices
 * @package   Microservices
 * @author    Ramesh N. Jangid (Sharma) <polygon.co.in@gmail.com>
 * @copyright © 2026 Ramesh N. Jangid (Sharma)
 * @license   MIT https://opensource.org/license/mit
 * @link      https://github.com/polygoncoin/Microservices
 * @since     Class available since Release 1.0.0
 */
class Microservices
{
	/**
	 * Start micro timestamp;
	 *
	 * @var null|int
	 */
	private $startMicroTimestamp = null;

	/**
	 * End micro timestamp;
	 *
	 * @var null|int
	 */
	private $endMicroTimestamp = null;

	/**
	 * HTTP request data
	 *
	 * @var null|array
	 */
	public $httpReqData = null;

	/**
	 * HTTP object
	 *
	 * @var null|Http
	 */
	public $httpObj = null;

	/**
	 * Constructor
	 *
	 * @param array $httpReqData HTTP request data
	 * @throws \Exception
	 */
	public function __construct(
		&$httpReqData
	) {
		$this->httpReqData = &$httpReqData;
		$this->httpObj = new Http(
			$this->httpReqData
		);
	}

	/**
	 * Initialize
	 *
	 * @return bool
	 */
	public function init(): bool
	{
		if (Env::$OUTPUT_PERFORMANCE_STATS) {
			$this->startMicroTimestamp = microtime(as_float: true);
		}

		return $this->httpObj->init();
	}

	/**
	 * Process
	 *
	 * @return mixed
	 * @throws \Exception
	 */
	public function process(): mixed
	{
		$this->httpObj->initRequest();

		$class = null;

		switch (true) {
			case $this->httpReqData['get'][ROUTE_URL_PARAM] === '/logout':
				$class = __NAMESPACE__ . '\\Logout';
				break;

			// Generates auth token
			case $this->httpReqData['get'][ROUTE_URL_PARAM] === '/login':
				$class = __NAMESPACE__ . '\\Login';
				break;

			// Requires auth token
			default:
				$gateway = new Gateway(
					httpObj: $this->httpObj
				);
				$gateway->init();
				$gateway = null;

				$class = __NAMESPACE__ . '\\Api';
				break;
		}

		// Class found
		try {
			if ($class !== null) {
				$api = new $class(
					httpObj: $this->httpObj
				);
				if ($api->init()) {
					$this->startData();
					$return = $api->process();
					if (
						is_array(
							value: $return
						)
						&& count(
							value: $return
						) === 3
					) {
						return $return;
					}
					$this->addStatus();
					$this->addPerformance();
					$this->endData();
				}
			}
		} catch (\Exception $e) {
			$this->manageException(
				e: $e
			);
		}

		return true;
	}

	/**
	 * Start Data Output
	 *
	 * @return void
	 */
	public function startData(): void
	{
		if ($this->httpObj->responseObj === null) {
			return;
		}
		$this->httpObj->responseObj->dataEncodeObj->startObject();
	}

	/**
	 * Add HTTP status in response
	 *
	 * @return void
	 */
	public function addStatus(): void
	{
		if ($this->httpObj->responseObj === null) {
			return;
		}
		$this->httpObj->responseObj->dataEncodeObj->addKeyData(
			objectKey: 'Status',
			data: $this->httpObj->responseObj->httpStatus
		);
	}

	/**
	 * Add Performance detail in response
	 *
	 * @return void
	 */
	public function addPerformance(): void
	{
		if ($this->httpObj->responseObj === null) {
			return;
		}
		if (Env::$OUTPUT_PERFORMANCE_STATS) {
			$this->endMicroTimestamp = microtime(as_float: true);
			$time = ceil(
				num: ($this->endMicroTimestamp - $this->startMicroTimestamp) * 1000
			);
			$memory = ceil(
				num: memory_get_peak_usage() / 1000
			);

			$this->httpObj->responseObj->dataEncodeObj->startObject(
				objectKey: 'Stats'
			);
			$this->httpObj->responseObj->dataEncodeObj->startObject(
				objectKey: 'Performance'
			);
			$this->httpObj->responseObj->dataEncodeObj->addKeyData(
				objectKey: 'total-time-taken',
				data: "{$time} ms"
			);
			$this->httpObj->responseObj->dataEncodeObj->addKeyData(
				objectKey: 'peak-memory-usage',
				data: "{$memory} KB"
			);
			$this->httpObj->responseObj->dataEncodeObj->endObject();
			$this->httpObj->responseObj->dataEncodeObj->addKeyData(
				objectKey: 'getrusage',
				data: getrusage()
			);
			$this->httpObj->responseObj->dataEncodeObj->endObject();
		}
	}

	/**
	 * Add Performance detail in response
	 *
	 * @return array
	 */
	public function returnPerformance(): array
	{
		if ($this->httpObj->responseObj === null) {
			return [];
		}
		$returnPerformance = [];
		if (Env::$OUTPUT_PERFORMANCE_STATS) {
			$this->endMicroTimestamp = microtime(as_float: true);
			$time = ceil(
				num: ($this->endMicroTimestamp - $this->startMicroTimestamp) * 1000
			);
			$memory = ceil(
				num: memory_get_peak_usage() / 1000
			);

			$returnPerformance = [
				'Stats' => [
					'Performance' => [
						'total-time-taken' => "{$time} ms",
						'peak-memory-usage' => "{$memory} KB"
					],
					'getrusage' => getrusage()
				]
			];
		}

		return $returnPerformance;
	}

	/**
	 * End response
	 *
	 * @return void
	 */
	public function endData(): void
	{
		if ($this->httpObj->responseObj === null) {
			return;
		}
		$this->httpObj->responseObj->dataEncodeObj->endObject();
		$this->httpObj->responseObj->dataEncodeObj->end();
	}

	/**
	 * Output response
	 *
	 * @return void
	 */
	public function outputResults(): void
	{
		if ($this->httpObj->responseObj === null) {
			return;
		}
		http_response_code(response_code: $this->httpObj->responseObj->httpStatus);
		$this->httpObj->responseObj->dataEncodeObj->streamData();
	}

	/**
	 * Return encoded result
	 *
	 * @return bool|string
	 */
	public function returnResults(): bool|string
	{
		if ($this->httpObj->responseObj === null) {
			return false;
		}
		return $this->httpObj->responseObj->dataEncodeObj->getData();
	}

	/**
	 * Headers / CORS
	 *
	 * @return array
	 */
	public function getHeaders(): array
	{
		$headerArr = [];

		// $headerArr['Access-Control-Allow-Origin'] = $this->httpReqData['server']['domainName'];
		$headerArr['Vary'] = 'Origin';
		$headerArr['Access-Control-Allow-Headers'] = '*';

		$headerArr['Referrer-Policy'] = 'origin';
		$headerArr['X-Frame-Options'] = 'SAMEORIGIN';
		$headerArr['X-Content-Type-Options'] = 'nosniff';
		$headerArr['Cross-Origin-Resource-Policy'] = 'same-origin';
		$headerArr['Cross-Origin-Embedder-Policy'] = 'unsafe-none';
		$headerArr['Cross-Origin-Opener-Policy'] = 'unsafe-none';

		// Access-Control header are received during OPTIONS request
		if ($this->httpReqData['server']['httpMethod'] === Constant::$OPTIONS) {
			// may also be using PUT, PATCH, HEAD etc
			$methods = 'GET, QUERY, POST, PUT, PATCH, DELETE, OPTIONS';
			$headerArr['Access-Control-Allow-Methods'] = $methods;
		} else {
			if ($this->httpObj->responseObj === null) {
				$outputRepresentation = Env::$outputRepresentation;
			} else {
				$outputRepresentation = $this->httpObj->responseObj->outputRepresentation;
			}
			switch ($outputRepresentation) {
				case 'XML':
				case 'XSLT':
					$headerArr['Content-Type'] = 'text/xml; charset=utf-8';
					break;
				case 'JSON':
					$headerArr['Content-Type'] = 'application/json; charset=utf-8';
					break;
				case 'HTML':
				case 'PHP':
					$headerArr['Content-Type'] = 'text/html; charset=utf-8';
					break;
			}
			$cacheControl = 'no-store, no-cache, must-revalidate, max-age=0';
			$headerArr['Cache-Control'] = $cacheControl;
			$headerArr['Pragma'] = 'no-cache';
		}

		return $headerArr;
	}

	/**
	 * Log error
	 *
	 * @param \Exception $e Exception
	 *
	 * @return never
	 * @throws \Exception
	 */
	private function manageException(
		$e
	): never {
		throw new \Exception(
			message: $e->getMessage(),
			code: $e->getCode()
		);
	}
}
