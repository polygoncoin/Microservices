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

use Microservices\App\AppTrait;
use Microservices\App\CacheServerKey;
use Microservices\App\DbCommonFunction;
use Microservices\App\CommonFunction;

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
	use AppTrait;

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
	 * @return bool
	 */
	public function process(): bool
	{
		DbCommonFunction::connectGlobalCacheServer();
		DbCommonFunction::connectGlobalDatabaseServer();

		DbCommonFunction::$gDbServer->execDbQuery(
			sql: "
				SELECT
					 *
				FROM
					`{$this->execPhpFunc(param: getenv(name: 'customerTable'))}` C
				",
			params: []
		);
		$cRows = DbCommonFunction::$gDbServer->fetchAll();
		DbCommonFunction::$gDbServer->closeCursor();
		foreach ($cRows as $cRow) {
			if ($cRow['allowed_cidr'] !== null) {
				$cCidrs = CommonFunction::cidrsIpNumber(cidrString: $cRow['allowed_cidr']);
				if (count(value: $cCidrs) > 0) {
					$cCidrKey = CacheServerKey::customerCidr(cID: $cRow['id']);
					DbCommonFunction::$gCacheServer->setCache(
						key: $cCidrKey,
						value: json_encode(value: $cCidrs)
					);
				}
			}
			if (!empty($cRow['open_api_domain'])) {
				$c_key = CacheServerKey::openToWebDomain(
					domainName: $cRow['open_api_domain']
				);
				DbCommonFunction::$gCacheServer->setCache(
					key: $c_key,
					value: json_encode(value: $cRow)
				);
			}
			$c_key = CacheServerKey::closedToWebDomain(domainName: $cRow['api_domain']);
			DbCommonFunction::$gCacheServer->setCache(
				key: $c_key,
				value: json_encode(value: $cRow)
			);
			$dbServerObj = DbCommonFunction::connectDatabaseServer(
				dbServerType: getenv(name: $cRow['master_db_server_type']),
				dbServerHostname: getenv(name: $cRow['master_db_server_hostname']),
				dbServerPort: getenv(name: $cRow['master_db_server_port']),
				dbServerUsername: getenv(name: $cRow['master_db_server_username']),
				dbServerPassword: getenv(name: $cRow['master_db_server_password']),
				dbServerDB: getenv(name: $cRow['master_db_server_db'])
			);

			// Groups
			$dbServerObj->execDbQuery(
				sql: "
					SELECT
						 *
					FROM
						`{$cRow['groupsTable']}` U
					",
				params: []
			);
			$gRows = $dbServerObj->fetchAll();
			$dbServerObj->closeCursor();

			foreach ($gRows as $gRow) {
				$g_key = CacheServerKey::customerGroup(
					cID: $cRow['id'],
					gID: $gRow['id']
				);
				DbCommonFunction::$gCacheServer->setCache(key: $g_key, value: json_encode(value: $gRow));
				if ($gRow['allowed_cidr'] !== null) {
					$cidrs = CommonFunction::cidrsIpNumber(cidrString: $gRow['allowed_cidr']);
					if (count(value: $cidrs) > 0) {
						$cidrKey = CacheServerKey::customerGroupCidr(
							cID: $cRow['id'],
							gID: $gRow['id']
						);
						DbCommonFunction::$gCacheServer->setCache(
							key: $cidrKey,
							value: json_encode(value: $cidrs)
						);
					}
				}
			}

			// Users
			$dbServerObj->execDbQuery(
				sql: "
					SELECT
						 *
					FROM
						`{$cRow['usersTable']}` U
					",
				params: []
			);
			$uRows = $dbServerObj->fetchAll();
			$dbServerObj->closeCursor();
			foreach ($uRows as $uRow) {
				if ($uRow['allowed_cidr'] !== null) {
					$uCidrs = CommonFunction::cidrsIpNumber(cidrString: $uRow['allowed_cidr']);
					if (count(value: $uCidrs) > 0) {
						$uCidrKey = CacheServerKey::customerUserCidr(
							cID: $cRow['id'],
							uID: $uRow['id']
						);
						DbCommonFunction::$gCacheServer->setCache(
							key: $uCidrKey,
							value: json_encode(value: $uCidrs)
						);
					}
				}
				$cu_key = CacheServerKey::customerUsername(
					cID: $cRow['id'],
					username: $uRow['username']
				);
				DbCommonFunction::$gCacheServer->setCache(
					key: $cu_key,
					value: json_encode(value: $uRow)
				);
			}
		}

		return true;
	}

	/**
	 * Remove token from cache
	 *
	 * @param string $token Token to be delete from cache
	 *
	 * @return void
	 */
	private function processToken($token): void
	{
		DbCommonFunction::$gCacheServer->deleteCache(key: CacheServerKey::token(token: $token));
	}
}
