<?php

/**
 * Common Function File
 * php version 8.3
 * 
 * @category  Common Function
 * @package   Microservices
 * @author    Ramesh N. Jangid (Sharma) <polygon.co.in@gmail.com>
 * @copyright © 2026 Ramesh N. Jangid (Sharma)
 * @license   MIT https://opensource.org/license/mit
 * @link      https://github.com/polygoncoin/Microservices
 * @since     Class available since Release 1.0.0
 */

namespace Microservices\App;

use Microservices\App\CacheServerKey;
use Microservices\App\Constant;
use Microservices\App\DbCommonFunction;
use Microservices\App\Http;
use Microservices\App\HttpStatus;
use Microservices\App\Server\CacheServer\CacheServerInterface;

/**
 * Common Function File
 * php version 8.3
 * 
 * @category  Common Function
 * @package   Microservices
 * @author    Ramesh N. Jangid (Sharma) <polygon.co.in@gmail.com>
 * @copyright © 2026 Ramesh N. Jangid (Sharma)
 * @license   MIT https://opensource.org/license/mit
 * @link      https://github.com/polygoncoin/Microservices
 * @since     Class available since Release 1.0.0
 */
class CommonFunction
{
	/**
	 * Check Feature is Enabled (Yes/No)
	 * 
	 * @param Http   $httpObj
	 * @param string $feature
	 * 
	 * @return bool
	 */
	public static function isEnabled(
		&$httpObj,
		$feature
	): bool {
		if (!isset($httpObj->httpRequestObj->session['customerData'][$feature])) {
			throw new \Exception(
				message: "Provided feature '{$feature}' not found",
				code: HttpStatus::$InternalServerError
			);
		}
		if (empty($httpObj->httpRequestObj->session['customerData'][$feature])) {
			return false;
		} else {
			return ($httpObj->httpRequestObj->session['customerData'][$feature] === Constant::$YES) ? Constant::$TRUE : Constant::$FALSE;
		}
	}

	/**
	 * Check Errors related to File Upload
	 * 
	 * @param array $httpFileArr $httpReqData['files']
	 * 
	 * @return void
	 * @throws \Exception
	 */
	public static function validateFileUpload(
		$httpFileArr
	): void {
		if (
			count(
				value: $httpFileArr
			) > 1
		) {
			throw new \Exception(
				message: 'Supports only one file with each request',
				code: HttpStatus::$BadRequest
			);
		}

		foreach ($httpFileArr as $file => $detail) {
			if (isset($detail['error'])) {
				switch ($detail['error']) {
					case \UPLOAD_ERR_INI_SIZE: // value 1
						throw new \Exception(
							message: 'Size of the uploaded file exceeds the maximum value specified',
							code: HttpStatus::$InternalServerError
						);
						break;

					case \UPLOAD_ERR_FORM_SIZE: // value 2
						throw new \Exception(
							message: 'Size of the uploaded file exceeds the maximum value specified in the HTML form in the MAX_FILE_SIZE element',
							code: HttpStatus::$BadRequest
						);
						break;

					case \UPLOAD_ERR_PARTIAL: // value 3
						throw new \Exception(
							message: 'The file was only partially uploaded',
							code: HttpStatus::$InternalServerError
						);
						break;

					case \UPLOAD_ERR_NO_FILE: // value 4
						throw new \Exception(
							message: 'No file was uploaded',
							code: HttpStatus::$InternalServerError
						);
						break;

					case \UPLOAD_ERR_NO_TMP_DIR: // value 6
						throw new \Exception(
							message: 'No temporary directory is specified',
							code: HttpStatus::$InternalServerError
						);
						break;

					case \UPLOAD_ERR_CANT_WRITE: // value 7
						throw new \Exception(
							message: 'Writing the file to disk failed',
							code: HttpStatus::$InternalServerError
						);
						break;

					case \UPLOAD_ERR_EXTENSION: // value 8
						throw new \Exception(
							message: 'An extension stopped the file upload process',
							code: HttpStatus::$InternalServerError
						);
						break;
				}
			}
		}
	}

	/**
	 * Returns start and end IP number for a given CIDR
	 * 
	 * @param string $cidrString IP address range in CIDR notation for check
	 * 
	 * @return array
	 */
	public static function cidrStringIpNumberRange(
		$cidrString
	): array {
		$response = [];

		if (empty($cidrString)) {
			return $response;
		}

		foreach (
			explode(
				separator: ',',
				string: str_replace(
					search: ' ',
					replace: '',
					subject: $cidrString
				)
			) as $cidr
		) {
			$cidr = trim($cidr);
			if (
				empty($cidr)
				|| $cidr === '0.0.0.0/0'
			) {
				continue;
			}
			if (
				strpos(
					haystack: $cidr,
					needle: '/'
				)
			) {
				[$cidrIp, $bits] = explode(
					separator: '/',
					string: str_replace(
						search: ' ',
						replace: '',
						subject: $cidr
					)
				);
				$binCidrIpStr = str_pad(
					string: decbin(
						num: ip2long(
							ip: $cidrIp
						)
					),
					length: 32,
					pad_string: 0,
					pad_type: STR_PAD_LEFT
				);
				$startIpNumber = bindec(
					binary_string: str_pad(
						string: substr(
							string: $binCidrIpStr,
							offset: 0,
							length: $bits
						),
						length: 32,
						pad_string: 0,
						pad_type: STR_PAD_RIGHT
					)
				);
				$endIpNumber = $startIpNumber + pow(
					num: 2,
					exponent: $bits
				) - 1;
				$response[] = [
					'start' => $startIpNumber,
					'end' => $endIpNumber
				];
			} else {
				if (
					$ipNumber = ip2long(
						ip: $cidr
					)
				) {
					$response[] = [
						'start' => $ipNumber,
						'end' => $ipNumber
					];
				}
			}
		}

		return $response;
	}

	/**
	 * Check IP with CIDR based on cache key containing start and end IP number
	 * 
	 * @param CacheServerInterface $cacheObj     Cache Server object
	 * @param string               $ip           Request Ip
	 * @param string               $cidrCacheKey Cache Key(s)
	 * 
	 * @return void
	 * @throws \Exception
	 */
	public static function checkCacheCidr(
		$cacheObj,
		$ip,
		$cidrCacheKey
	): void {
		if (
			!$cacheObj->cacheExist(
				cacheKey: $cidrCacheKey
			)
		) {
			return;
		}

		$cidrIpNumberRangeArr = $cacheObj->cacheGet(
			cacheKey: $cidrCacheKey
		);
		$isValidIp = self::belongsToCidrIpNumberRange(
			ip: $ip,
			cidrIpNumberRangeArr: $cidrIpNumberRangeArr
		);
		if (!$isValidIp) {
			throw new \Exception(
				message: 'IP not supported',
				code: HttpStatus::$BadRequest
			);
		}
	}

	/**
	 * Check IP with CIDR
	 * 
	 * @param string $ip         Request Ip
	 * @param string $cidrString CIDRs
	 * 
	 * @return null|bool
	 * @throws \Exception
	 */
	public static function checkCidr(
		$ip,
		$cidrString
	): null|bool {
		$isValidIp = true;
		$cidrIpNumberRangeArr = self::cidrStringIpNumberRange(
			cidrString: $cidrString
		);
		if (
			count(
				value: $cidrIpNumberRangeArr
			) > 0
		) {
			$isValidIp = self::belongsToCidrIpNumberRange(
				ip: $ip,
				cidrIpNumberRangeArr: $cidrIpNumberRangeArr
			);
			if (!$isValidIp) {
				throw new \Exception(
					message: 'IP not supported',
					code: HttpStatus::$BadRequest
				);
			}
		}

		return $isValidIp;
	}

	/**
	 * Belongs to Cidr IP number range
	 * 
	 * @param string $ip                   IP
	 * @param array  $cidrIpNumberRangeArr Cidr IP number ranges
	 * 
	 * @return bool
	 */
	public static function belongsToCidrIpNumberRange(
		$ip,
		$cidrIpNumberRangeArr
	): bool {
		$isValidIp = false;
		if (
			count(
				value: $cidrIpNumberRangeArr
			) === 0
		) {
			return $isValidIp;
		}

		$ipNumber = ip2long(
			ip: $ip
		);

		foreach ($cidrIpNumberRangeArr as $cidrIpNumber) {
			if (
				$cidrIpNumber['start'] === 0
				&& $cidrIpNumber['end'] === 0
			) {
				$isValidIp = true;
				break;
			} elseif (
				$cidrIpNumber['start'] <= $ipNumber
				&& $ipNumber <= $cidrIpNumber['end']
			) {
				$isValidIp = true;
				break;
			}
		}

		return $isValidIp;
	}

	/**
	 * Validate remote IP
	 * 
	 * @param Http $httpObj
	 * 
	 * @return void
	 */
	public static function checkPrivateRequestCidr(
		&$httpObj
	): void {
		if (
			!self::isEnabled(
				httpObj: $httpObj,
				feature: 'customer_enabled_cidr_check'
			)
		) {
			return;
		}

		self::checkCacheCidr(
			cacheObj: DbCommonFunction::$globalCacheServerObj,
			ip: $httpObj->httpReqData['server']['httpRequestIp'],
			cidrCacheKey: CacheServerKey::customerCidr(
				customerId: $httpObj->httpRequestObj->customerId
			)
		);

		if ($httpObj !== Constant::$NULL) {
			self::checkCacheCidr(
				cacheObj: $httpObj->httpRequestObj->customerCacheObj,
				ip: $httpObj->httpReqData['server']['httpRequestIp'],
				cidrCacheKey: CacheServerKey::customerGroupCidr(
					customerId: $httpObj->httpRequestObj->customerId,
					customerUserGroupId: $httpObj->httpRequestObj->customerUserGroupId
				)
			);

			self::checkCacheCidr(
				cacheObj: $httpObj->httpRequestObj->customerCacheObj,
				ip: $httpObj->httpReqData['server']['httpRequestIp'],
				cidrCacheKey: CacheServerKey::customerUserCidr(
					customerId: $httpObj->httpRequestObj->customerId,
					customerUserId: $httpObj->httpRequestObj->customerUserId
				)
			);
		}
	}

	/**
	 * JSON Decode
	 * 
	 * @param mixed $value
	 * 
	 * @return mixed
	 */
	public static function jsonDecode(
		$value
	): mixed {
		$isArray = str_starts_with(
			haystack: $value,
			needle: '['
		);
		$isObject = str_starts_with(
			haystack: $value,
			needle: '{'
		);

		if ($isArray || $isObject) {
			$value = json_decode(
				json: $value,
				associative: Constant::$TRUE
			);
		}

		return $value;
	}
}
