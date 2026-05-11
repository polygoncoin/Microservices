<?php

/**
 * CustomAPI
 * php version 8.3
 *
 * @category  CustomAPI
 * @package   Microservices
 * @author    Ramesh N. Jangid (Sharma) <polygon.co.in@gmail.com>
 * @copyright © 2026 Ramesh N. Jangid (Sharma)
 * @license   MIT https://opensource.org/license/mit
 * @link      https://github.com/polygoncoin/Microservices
 * @since     Class available since Release 1.0.0
 */

namespace Microservices\www\Supplement\Custom;

use Microservices\App\DbCommonFunction;
use Microservices\App\Http;
use Microservices\www\Supplement\Custom\CustomInterface;
use Microservices\www\Supplement\Custom\CustomTrait;

/**
 * CustomAPI Category
 * php version 8.3
 *
 * @category  CustomAPI_Category
 * @package   Microservices
 * @author    Ramesh N. Jangid (Sharma) <polygon.co.in@gmail.com>
 * @copyright © 2026 Ramesh N. Jangid (Sharma)
 * @license   MIT https://opensource.org/license/mit
 * @link      https://github.com/polygoncoin/Microservices
 * @since     Class available since Release 1.0.0
 */
class Category implements CustomInterface
{
	use CustomTrait;

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
		DbCommonFunction::connectClientDb($this->http->req, fetchFrom: 'Slave');
	}

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
	 * @param array $payload Payload
	 *
	 * @return array
	 */
	public function process(array $payload = []): array
	{
		$sql = '
			SELECT *
			FROM category
			WHERE is_deleted = :is_deleted AND parent_id = :parent_id
		';
		$sqlParamArr = [
			':is_deleted' => 'No',
			':parent_id' => 0,
		];
		DbCommonFunction::$slaveDb[$this->http->req->cID]->execDbQuery(sql: $sql, paramArr: $sqlParamArr);
		$rowArr = DbCommonFunction::$slaveDb[$this->http->req->cID]->fetchAll();
		DbCommonFunction::$slaveDb[$this->http->req->cID]->closeCursor();
		$this->http->res->dataEncode->addKeyData(objectKey: 'Results', data: $rowArr);
		return [true];
	}
}
