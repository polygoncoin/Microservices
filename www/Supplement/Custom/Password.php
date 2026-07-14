<?php

/**
 * CustomAPI
 * php version 8.3
 *
 * @category  CustomAPI_Interface
 * @package   Microservices
 * @author    Ramesh N. Jangid (Sharma) <polygon.co.in@gmail.com>
 * @copyright © 2026 Ramesh N. Jangid (Sharma)
 * @license   MIT https://opensource.org/license/mit
 * @link      https://github.com/polygoncoin/Microservices
 * @since     Class available since Release 1.0.0
 */

namespace Microservices\www\Supplement\Custom;

use Microservices\App\CacheServerKey;
use Microservices\App\Http;
use Microservices\App\Reload;
use Microservices\www\Supplement\Custom\CustomInterface;
use Microservices\www\Supplement\Custom\CustomTrait;

/**
 * CustomAPI Password
 * php version 8.3
 *
 * @category  CustomAPI_Password
 * @package   Microservices
 * @author    Ramesh N. Jangid (Sharma) <polygon.co.in@gmail.com>
 * @copyright © 2026 Ramesh N. Jangid (Sharma)
 * @license   MIT https://opensource.org/license/mit
 * @link      https://github.com/polygoncoin/Microservices
 * @since     Class available since Release 1.0.0
 */
class Password implements CustomInterface
{
	use CustomTrait;

	/**
	 * HTTP object
	 *
	 * @var null|Http
	 */
	private $httpObj = null;

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
	 * @return bool
	 */
	public function init(): bool
	{
		$this->httpObj->requestObj->loadPayload();

		return true;
	}

	/**
	 * Process
	 *
	 * @return mixed
	 */
	public function process(): mixed
	{
		switch ($this->httpObj->requestObj->session['payloadType']) {
			case 'Array':
				$payload = $this->httpObj->requestObj->dataDecodeObj->get('0');
				break;
			case 'Object':
				$payload = $this->httpObj->requestObj->dataDecodeObj->get();
				break;
		}
		$this->httpObj->requestObj->session['payload'] = $payload;

		$oldPassword = $this->httpObj->requestObj->session['payload']['old_password'];
		$oldPasswordHash = $this->httpObj->requestObj->session['userData']['password_hash'];

		if (
			password_verify(
				password: $oldPassword,
				hash: $oldPasswordHash
			)
		) {
			$userName = $this->httpObj->requestObj->session['userData']['username'];
			$newPassword = $this->httpObj->requestObj->session['payload']['new_password'];
			$newPasswordHash = password_hash(
				password: $newPassword,
				algo: PASSWORD_DEFAULT
			);

			$sql = "
				UPDATE `{$this->httpObj->requestObj->session['customerData']['customer_user_table']}`
				SET password_hash = :password_hash
				WHERE username = :username AND is_deleted = :is_deleted
			";
			$paramArr = [
				':password_hash' => $newPasswordHash,
				':username' => $userName,
				':is_deleted' => 'No',
			];

			$this->httpObj->requestObj->customerDbObj->execQuery(
				sql: $sql,
				paramArr: $paramArr
			);
			$this->httpObj->requestObj->customerDbObj->closeCursor();

			$customerId = $this->httpObj->requestObj->customerId;
			$cacheKey = CacheServerKey::customerUsername(
				customerId: $customerId,
				username: $userName
			);
			Reload::processUser(
				httpRequestIp: $this->httpObj->httpReqData['server']['httpRequestIp'],
				customerData: $this->httpObj->requestObj->session['customerData'],
				customerUserId: $this->httpObj->requestObj->customerUserId
			);
			$this->httpObj->requestObj->customerCacheObj->cacheDelete(
				cacheKey: CacheServerKey::token(
					token: $this->httpObj->requestObj->session['authId']
				)
			);

			$this->httpObj->responseObj->dataEncodeObj->addKeyData(
				objectKey: 'Results',
				data: 'Password changed successfully. Please login'
			);
		}

		return true;
	}
}
