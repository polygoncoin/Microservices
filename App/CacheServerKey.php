<?php

/**
 * Cache Server Key
 * php version 8.3
 *
 * @category  Cache Server Key
 * @package   Microservices
 * @author    Ramesh N. Jangid (Sharma) <polygon.co.in@gmail.com>
 * @copyright © 2026 Ramesh N. Jangid (Sharma)
 * @license   MIT https://opensource.org/license/mit
 * @link      https://github.com/polygoncoin/Microservices
 * @since     Class available since Release 1.0.0
 */

namespace Microservices\App;

/**
 * Cache Server Key
 * php version 8.3
 *
 * @category  Cache Server Key
 * @package   Microservices
 * @author    Ramesh N. Jangid (Sharma) <polygon.co.in@gmail.com>
 * @copyright © 2026 Ramesh N. Jangid (Sharma)
 * @license   MIT https://opensource.org/license/mit
 * @link      https://github.com/polygoncoin/Microservices
 * @since     Class available since Release 1.0.0
 */
class CacheServerKey
{
	/**
	 * Get Customer Key
	 *
	 * @param string $domainName Domain Name
	 *
	 * @return string
	 */
	public static function openToWebDomain(&$domainName): string
	{
		return "otw:{$domainName}";
	}

	/**
	 * Get Customer Key
	 *
	 * @param string $domainName Domain Name
	 *
	 * @return string
	 */
	public static function closedToWebDomain($domainName): string
	{
		return "ctw:{$domainName}";
	}

	/**
	 * Get Customer User Key
	 *
	 * @param int    $cID      Customer ID
	 * @param string $username Username
	 *
	 * @return string
	 */
	public static function customerUsername($cID, $username): string
	{
		return "c:{$cID}:u:{$username}";
	}

	/**
	 * Get Group Key
	 *
	 * @param int $cID Customer ID
	 * @param int $gID Group ID
	 *
	 * @return string
	 */
	public static function customerGroup($cID, $gID): string
	{
		return "c:{$cID}:g:{$gID}";
	}

	/**
	 * Get Customer CIDR Key
	 *
	 * @param int $cID Customer ID
	 *
	 * @return string
	 */
	public static function customerCidr($cID): string
	{
		return "c:{$cID}:cidr";
	}

	/**
	 * Get Group CIDR Key
	 *
	 * @param int $cID Customer ID
	 * @param int $gID Group ID
	 *
	 * @return string
	 */
	public static function customerGroupCidr($cID, $gID): string
	{
		return "c:{$cID}:g:{$gID}:cidr";
	}

	/**
	 * Get User CIDR Key
	 *
	 * @param int $cID Customer ID
	 * @param int $uID User ID
	 *
	 * @return string
	 */
	public static function customerUserCidr($cID, $uID): string
	{
		return "c:{$cID}:u:{$uID}:cidr";
	}

	/**
	 * Get Token Key
	 *
	 * @param string $token Token
	 *
	 * @return string
	 */
	public static function token($token): string
	{
		return "t:{$token}";
	}

	/**
	 * Get User Token Key
	 *
	 * @param int $cID Customer ID
	 * @param int $uID User ID
	 *
	 * @return string
	 */
	public static function customerUserToken($cID, $uID): string
	{
		return "c:{$cID}:u:{$uID}:token";
	}

	/**
	 * Get User Token Key
	 *
	 * @param int $cID Customer ID
	 * @param int $uID User ID
	 *
	 * @return string
	 */
	public static function customerUserSessionID($cID, $uID): string
	{
		return "c:{$cID}:u:{$uID}:sID";
	}

	/**
	 * Get Key maintaining Concurrency Interval(active session) For Current User
	 *
	 * @param int $cID Customer ID
	 * @param int $uID User ID
	 *
	 * @return string
	 */
	public static function customerUserConcurrency($cID, $uID): string
	{
		return "c:{$cID}:u:{$uID}:con";
	}

	/**
	 * Key to maintain Referrer Lag
	 *
	 * @param int $cID Customer ID
	 * @param int $uID User ID
	 *
	 * @return string
	 */
	public static function customerUserReferrerLag($cID, $uID): string
	{
		return "c:{$cID}:u:{$uID}:rlag";
	}
}
