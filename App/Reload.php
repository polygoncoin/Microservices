<?php

/**
 * Load Cache Server Key
 * php version 8.3
 *
 * @category  Reload
 * @package   Microservices
 * @author    Ramesh N. Jangid (Sharma) <polygon.co.in@gmail.com>
 * @copyright © 2026 Ramesh N. Jangid (Sharma)
 * @license   MIT https://opensource.org/license/mit
 * @link      https://github.com/polygoncoin/Microservices
 * @since     Class available since Release 1.0.0
 */

namespace Microservices\App;

use Microservices\App\CacheServerKey;
use Microservices\App\CommonFunction;
use Microservices\App\DbCommonFunction;
use Microservices\App\Env;

/**
 * Load Cache Server Key
 * php version 8.3
 *
 * @category  Reload
 * @package   Microservices
 * @author    Ramesh N. Jangid (Sharma) <polygon.co.in@gmail.com>
 * @copyright © 2026 Ramesh N. Jangid (Sharma)
 * @license   MIT https://opensource.org/license/mit
 * @link      https://github.com/polygoncoin/Microservices
 * @since     Class available since Release 1.0.0
 */
class Reload
{
	/**
	 * Process
	 *
	 * @param string $httpRequestIp Request Ip
	 *
	 * @return bool
	 */
	public static function process(
		$httpRequestIp
	): bool {
		DbCommonFunction::connectGlobalCache();
		DbCommonFunction::connectGlobalDb();

		return self::processCustomer(
			httpRequestIp: $httpRequestIp
		);
	}

	/**
	 * Cache Customer Data
	 *
	 * @param string   $httpRequestIp Request Ip
	 * @param null|int $customerId    Customer Id
	 *
	 * @return bool
	 */
	public static function processCustomer(
		$httpRequestIp,
		$customerId = null
	): bool {
		DbCommonFunction::connectGlobalCache();
		DbCommonFunction::connectGlobalDb();

		$customerTable = getenv(name: 'customerTable');

		$sql = "SELECT * FROM `{$customerTable}` C";
		$paramArr = [];

		if ($customerId > 0) {
			$sql = "SELECT * FROM `{$customerTable}` C WHERE customer_id = :customer_id";
			$paramArr[':customer_id'] = $customerId;
		}

		DbCommonFunction::$gDbServer->execQuery(
			sql: $sql,
			paramArr: $paramArr
		);
		$customerDataArr = DbCommonFunction::$gDbServer->fetchAll();
		DbCommonFunction::$gDbServer->closeCursor();
		foreach ($customerDataArr as $customerData) {
			CommonFunction::checkCidr(
				ip: $httpRequestIp,
				cidrString: Env::$reloadRestrictedCidr
			);

			if (!empty($customerData['customer_private_token_domain'])) {
				$privateTokenDomainCacheKey = CacheServerKey::privateTokenDomain(
					domainName: $customerData['customer_private_token_domain']
				);
				DbCommonFunction::$gCacheServer->cacheSet(
					cacheKey: $privateTokenDomainCacheKey,
					cacheValue: $customerData
				);
			}

			if (!empty($customerData['customer_private_session_domain'])) {
				$privateSessionDomainCacheKey = CacheServerKey::privateSessionDomain(
					domainName: $customerData['customer_private_session_domain']
				);
				DbCommonFunction::$gCacheServer->cacheSet(
					cacheKey: $privateSessionDomainCacheKey,
					cacheValue: $customerData
				);
			}

			if (!empty($customerData['customer_public_domain'])) {
				$publicDomainCacheKey = CacheServerKey::publicDomain(
					domainName: $customerData['customer_public_domain']
				);
				DbCommonFunction::$gCacheServer->cacheSet(
					cacheKey: $publicDomainCacheKey,
					cacheValue: $customerData
				);
			}

			if ($customerData['customer_allowed_cidr'] !== null) {
				$customerCidrIpNumberRangeArr = CommonFunction::cidrStringIpNumberRange(
					cidrString: $customerData['customer_allowed_cidr']
				);
				if (count(value: $customerCidrIpNumberRangeArr) > 0) {
					$customerCidrCacheKey = CacheServerKey::customerCidr(
						customerId: $customerData['customer_id']
					);
					DbCommonFunction::$gCacheServer->cacheSet(
						cacheKey: $customerCidrCacheKey,
						cacheValue: $customerCidrIpNumberRangeArr
					);
				}
			}

			self::processGroup(
				httpRequestIp: $httpRequestIp,
				customerData: $customerData
			);
			self::processUser(
				httpRequestIp: $httpRequestIp,
				customerData: $customerData
			);
		}

		return true;
	}

	/**
	 * Cache Group Data
	 *
	 * @param string   $httpRequestIp       Request Ip
	 * @param array    $customerData        Customer Data
	 * @param null|int $customerUserGroupId Customer User Group Id
	 *
	 * @return bool
	 */
	public static function processGroup(
		$httpRequestIp,
		$customerData,
		$customerUserGroupId = null
	): bool {
		$customerCacheServerCred = DbCommonFunction::customerCacheServerCred(
			customerData: $customerData
		);
		$customerCacheObj = DbCommonFunction::connectCache(
			cacheServerType: $customerCacheServerCred['cacheServerType'],
			cacheServerHostname: $customerCacheServerCred['cacheServerHostname'],
			cacheServerPort: $customerCacheServerCred['cacheServerPort'],
			cacheServerUsername: $customerCacheServerCred['cacheServerUsername'],
			cacheServerPassword: $customerCacheServerCred['cacheServerPassword'],
			cacheServerDatabase: $customerCacheServerCred['cacheServerDatabase'],
			cacheServerTable: $customerCacheServerCred['cacheServerTable']
		);

		$customerMasterDatabaseServerCred = DbCommonFunction::customerMasterDatabaseServerCred(
			customerData: $customerData
		);
		$customerDbObj = DbCommonFunction::connectDb(
			dbServerType: $customerMasterDatabaseServerCred['dbServerType'],
			dbServerHostname: $customerMasterDatabaseServerCred['dbServerHostname'],
			dbServerPort: $customerMasterDatabaseServerCred['dbServerPort'],
			dbServerUsername: $customerMasterDatabaseServerCred['dbServerUsername'],
			dbServerPassword: $customerMasterDatabaseServerCred['dbServerPassword'],
			dbServerDatabase: $customerMasterDatabaseServerCred['dbServerDatabase']
		);

		$sql = "SELECT * FROM `{$customerData['customer_user_group_table']}` G";
		$paramArr = [];

		if ($customerUserGroupId > 0) {
			$sql = "SELECT * FROM `{$customerData['customer_user_group_table']}` G WHERE customer_user_group_id = :customer_user_group_id";
			$paramArr[':customer_user_group_id'] = $customerUserGroupId;
		}

		// Groups
		$customerDbObj->execQuery(
			sql: $sql,
			paramArr: $paramArr
		);
		$groupDataArr = $customerDbObj->fetchAll();
		$customerDbObj->closeCursor();

		foreach ($groupDataArr as $groupData) {
			$g_key = CacheServerKey::customerGroup(
				customerId: $customerData['customer_id'],
				customerUserGroupId: $groupData['customer_user_group_id']
			);
			$customerCacheObj->cacheSet(
				cacheKey: $g_key,
				cacheValue: $groupData
			);
			if ($groupData['customer_user_group_allowed_cidr'] !== null) {
				$groupCidrIpNumberRangeArr = CommonFunction::cidrStringIpNumberRange(
					cidrString: $groupData['customer_user_group_allowed_cidr']
				);
				if (count(value: $groupCidrIpNumberRangeArr) > 0) {
					$groupCidrCacheKey = CacheServerKey::customerGroupCidr(
						customerId: $customerData['customer_id'],
						customerUserGroupId: $groupData['customer_user_group_id']
					);
					$customerCacheObj->cacheSet(
						cacheKey: $groupCidrCacheKey,
						cacheValue: $groupCidrIpNumberRangeArr
					);
				}
			}
		}

		return true;
	}

	/**
	 * Cache User Data
	 *
	 * @param string   $httpRequestIp Request Ip
	 * @param array    $customerData  Customer Data
	 * @param null|int $customerUserId        User Id
	 *
	 * @return bool
	 */
	public static function processUser(
		$httpRequestIp,
		$customerData,
		$customerUserId = null
	): bool {
		$customerCacheServerCred = DbCommonFunction::customerCacheServerCred(
			customerData: $customerData
		);
		$customerCacheObj = DbCommonFunction::connectCache(
			cacheServerType: $customerCacheServerCred['cacheServerType'],
			cacheServerHostname: $customerCacheServerCred['cacheServerHostname'],
			cacheServerPort: $customerCacheServerCred['cacheServerPort'],
			cacheServerUsername: $customerCacheServerCred['cacheServerUsername'],
			cacheServerPassword: $customerCacheServerCred['cacheServerPassword'],
			cacheServerDatabase: $customerCacheServerCred['cacheServerDatabase'],
			cacheServerTable: $customerCacheServerCred['cacheServerTable']
		);

		$customerMasterDatabaseServerCred = DbCommonFunction::customerMasterDatabaseServerCred(
			customerData: $customerData
		);
		$customerDbObj = DbCommonFunction::connectDb(
			dbServerType: $customerMasterDatabaseServerCred['dbServerType'],
			dbServerHostname: $customerMasterDatabaseServerCred['dbServerHostname'],
			dbServerPort: $customerMasterDatabaseServerCred['dbServerPort'],
			dbServerUsername: $customerMasterDatabaseServerCred['dbServerUsername'],
			dbServerPassword: $customerMasterDatabaseServerCred['dbServerPassword'],
			dbServerDatabase: $customerMasterDatabaseServerCred['dbServerDatabase']
		);

		$sql = "SELECT * FROM `{$customerData['customer_user_table']}` U";
		$paramArr = [];

		if ($customerUserId > 0) {
			$sql = "SELECT * FROM `{$customerData['customer_user_table']}` U WHERE customer_user_id = :customer_user_id";
			$paramArr[':customer_user_id'] = $customerUserId;
		}

		// Groups
		$customerDbObj->execQuery(
			sql: $sql,
			paramArr: $paramArr
		);
		$userDataArr = $customerDbObj->fetchAll();
		$customerDbObj->closeCursor();
		foreach ($userDataArr as $userData) {
			if ($userData['customer_user_allowed_cidr'] !== null) {
				$userCidrIpNumberRangeArr = CommonFunction::cidrStringIpNumberRange(
					cidrString: $userData['customer_user_allowed_cidr']
				);
				if (count(value: $userCidrIpNumberRangeArr) > 0) {
					$userCidrCacheKey = CacheServerKey::customerUserCidr(
						customerId: $customerData['customer_id'],
						customerUserId: $userData['customer_user_id']
					);
					$customerCacheObj->cacheSet(
						cacheKey: $userCidrCacheKey,
						cacheValue: $userCidrIpNumberRangeArr
					);
				}
			}
			$cu_key = CacheServerKey::customerUsername(
				customerId: $customerData['customer_id'],
				username: $userData['customer_user_username']
			);
			$customerCacheObj->cacheSet(
				cacheKey: $cu_key,
				cacheValue: $userData
			);
		}

		return true;
	}
}
