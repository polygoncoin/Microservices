<?php

/**
 * Validator
 * php version 8.3
 *
 * @category  Validator
 * @package   Microservices
 * @author    Ramesh N. Jangid (Sharma) <polygon.co.in@gmail.com>
 * @copyright © 2026 Ramesh N. Jangid (Sharma)
 * @license   MIT https://opensource.org/license/mit
 * @link      https://github.com/polygoncoin/Microservices
 * @since     Class available since Release 1.0.0
 */

namespace Microservices\www\Validation;

use Microservices\App\DbCommonFunction;
use Microservices\App\Env;
use Microservices\App\Http;
use Microservices\www\Validation\ValidatorInterface;
use Microservices\www\Validation\ValidatorTrait;

/**
 * Customer Validator
 * php version 8.3
 *
 * @category  Validator_Customer
 * @package   Microservices
 * @author    Ramesh N. Jangid (Sharma) <polygon.co.in@gmail.com>
 * @copyright © 2026 Ramesh N. Jangid (Sharma)
 * @license   MIT https://opensource.org/license/mit
 * @link      https://github.com/polygoncoin/Microservices
 * @since     Class available since Release 1.0.0
 */
class CustomerValidator implements ValidatorInterface
{
	use ValidatorTrait;

	/**
	 * Http Object
	 *
	 * @var null|Http
	 */
	private $http = null;

	/**
	 * Constructor
	 *
	 * @param Http $http
	 */
	public function __construct(Http &$http)
	{
		$this->http = &$http;
	}

	/**
	 * Validate payload
	 *
	 * @param array $validationConfig Validation configuration
	 *
	 * @return array
	 */
	public function validate(&$validationConfig): array
	{
		$isValidData = true;
		$errorArr = [];
		foreach ($validationConfig as &$v) {
			$args = [];
			foreach ($v['fnArgs'] as $attr => [$mode, $key]) {
				if ($mode === 'custom') {
					$args[$attr] = $key;
				} else {
					$args[$attr] = $this->http->req->s[$mode][$key];
				}
			}
			$fn = $v['fn'];
			if (!$this->$fn($args)) {
				$errorArr[] = $v['errorMessage'];
				$isValidData = false;
			}
		}

		return [$isValidData, $errorArr];
	}

	/**
	 * Gets primary key count
	 *
	 * @param string $table   Table Name
	 * @param string $primary Primary Key
	 * @param int    $id      Primary ID
	 *
	 * @return int 0/1
	 */
	private function getPrimaryCount(&$table, $primary, &$id): int
	{
		$dbServerDB = DbCommonFunction::$masterDb[$this->http->req->cID]->dbServerDB;
		$sql = "
			SELECT count(1) as `count`
			FROM `{$dbServerDB}`.`{$table}`
			WHERE `{$primary}` = ?
		";
		$paramArr = [$id];
		DbCommonFunction::$masterDb[$this->http->req->cID]->execDbQuery(sql: $sql, params: $paramArr);
		return (int)(DbCommonFunction::$masterDb[$this->http->req->cID]->fetch())['count'];
	}

	/**
	 * Checks primary key exist
	 *
	 * @param array $args Arguments
	 *
	 * @return bool
	 */
	private function primaryKeyExist(&$args): bool
	{
		extract(array: $args);
		$sql = "SELECT count(1) as `count` FROM `{$table}` WHERE `{$primary}` = ?";
		$paramArr = [$id];
		DbCommonFunction::$masterDb[$this->http->req->cID]->execDbQuery(sql: $sql, params: $paramArr);
		$row = DbCommonFunction::$masterDb[$this->http->req->cID]->fetch();
		DbCommonFunction::$masterDb[$this->http->req->cID]->closeCursor();
		return ($row['count'] === 0) ? false : true;
	}
}
