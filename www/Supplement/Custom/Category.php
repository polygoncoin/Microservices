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

use Microservices\App\Constant;
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
		$this->httpObj->httpRequestObj->customerDbObj = DbCommonFunction::connectCustomerDb(
			customerData: $this->httpObj->httpRequestObj->activeRequestCollection['customerData'],
			fetchDbMode: 'Slave'
		);
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
	 * @return mixed
	 */
	public function process(): mixed
	{
		$sql = '
			SELECT * 
			FROM category
			WHERE is_deleted = :is_deleted AND parent_id = :parent_id
		';
		$paramArr = [
			':is_deleted' => Constant::$NO,
			':parent_id' => 0,
		];
		$this->httpObj->httpRequestObj->customerDbObj->execQuery(
			sql: $sql,
			paramArr: $paramArr
		);
		$rowArr = $this->httpObj->httpRequestObj->customerDbObj->fetchAll();
		$this->httpObj->httpRequestObj->customerDbObj->closeCursor();
		$this->httpObj->httpResponseObj->dataEncodeObj->addKeyData(
			objectKey: 'Results',
			data: $rowArr
		);

		return true;
	}
}
