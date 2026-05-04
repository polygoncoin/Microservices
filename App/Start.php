<?php

/**
 * Start
 * php version 8.3
 *
 * @category  Start
 * @package   Microservices
 * @author    Ramesh N. Jangid (Sharma) <polygon.co.in@gmail.com>
 * @copyright © 2026 Ramesh N. Jangid (Sharma)
 * @license   MIT https://opensource.org/license/mit
 * @link      https://github.com/polygoncoin/Microservices
 * @since     Class available since Release 1.0.0
 */

namespace Microservices\App;

use Microservices\App\Dropbox;
use Microservices\App\Constant;
use Microservices\App\Log;
use Microservices\App\DataRepresentation\DataEncode;
use Microservices\App\HttpStatus;
use Microservices\App\Microservices;

class Start
{
	/**
	 * Process http request data
	 *
	 * @param array $httpReqDetails Http request details
	 *
	 * @return array
	 */
	public static function http(&$httpReqDetails)
	{
		if ($httpReqDetails['server']['httpMethod'] === Constant::$GET) {
			$dropboxCache = new Dropbox(httpReqDetails: $httpReqDetails);
			if ($dropboxCache->init(mode: 'Open')) {
				// File exists - Serve from Dropbox
				return $dropboxCache->process();
			}
			$dropboxCache = null;
		}

		$headers = [];

		try {
			$Microservices = new Microservices(httpReqDetails: $httpReqDetails);

			if (
				$httpReqDetails['streamData']
				&& $httpReqDetails['server']['httpMethod'] == 'OPTIONS'
			) {
				// Setting CORS
				$headers = $Microservices->getHeaders();
				$data = '{}';
				$status = HttpStatus::$Ok;

				return [$headers, $data, $status];
			}

			if ($Microservices->init()) {
				// Setting CORS
				if ($httpReqDetails['streamData']) {
					$headers = $Microservices->getHeaders();
				}

				$return = $Microservices->process();
				if (
					is_array($return)
					&& count($return) === 3
				) {
					return $return;
				}

				$data = $Microservices->returnResults();
				$status = $Microservices->http->res->httpStatus;

				return [$headers, $data, $status];
			}
		} catch (\Exception $e) {
			if (!in_array(needle: $e->getCode(), haystack: [400, 429])) {
				list($usec, $sec) = explode(separator: ' ', string: microtime());
				$dateTime = date(
					format: 'Y-m-d H:i:s',
					timestamp: $sec
				) . substr(string: $usec, offset: 1);

				// Log request details
				$logDetails = [
					'LogType' => 'ERROR',
					'DateTime' => $dateTime,
					'httpReqDetails' => [
						'HttpCode' => $e->getCode(),
						'HttpMessage' => $e->getMessage()
					],
					'Details' => $Microservices->http->req->s
				];
				$logsObj = new Log();
				$logsObj->log(logDetails: $logDetails);
			}

			$headers = [];
			if ($e->getCode() == 429) {
				$headers['Retry-After'] = $e->getMessage();
				$arr = [
					'Status' => $e->getCode(),
					'Message' => 'Too Many Request',
					'RetryAfter' => $e->getMessage()
				];
			} else {
				$arr = [
					'Status' => $e->getCode(),
					'Message' => $e->getMessage()
				];
			}

			// $dataEncode = new DataEncode(httpReqDetails: $httpReqDetails);
			// $dataEncode->init();
			// $dataEncode->startObject();
			// $dataEncode->addKeyData(key: 'Error', data: $arr);

			// $data = $dataEncode->getData();
			$data = json_encode(['Error' => $arr]);
			$status = $e->getCode();

			return [$headers, $data, $status];
		}
	}
}
