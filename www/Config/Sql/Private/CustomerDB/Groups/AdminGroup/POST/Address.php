<?php

/**
 * API Query config
 * php version 8.3
 * 
 * @category  API_Query_Config
 * @package   Microservices
 * @author    Ramesh N. Jangid (Sharma) <polygon.co.in@gmail.com>
 * @copyright © 2026 Ramesh N. Jangid (Sharma)
 * @license   MIT https://opensource.org/license/mit
 * @link      https://github.com/polygoncoin/Microservices
 * @since     Class available since Release 1.0.0
 */

use Microservices\App\Constant;
use Microservices\App\DatabaseServerDataType;

return [
	'__QUERY__' => 'INSERT INTO `address` SET __SET__',
	'__SET__' => [
		[
			'column' => 'customer_id',
			'activeRequestDataKey' => 'customerData',
			'activeRequestDataKeySubKey' => 'customer_id'
		],
		[
			'column' => 'customer_user_id',
			'activeRequestDataKey' => 'payload',
			'activeRequestDataKeySubKey' => 'id',
			'dataType' => DatabaseServerDataType::$INT
		],
		[
			'column' => 'address',
			'activeRequestDataKey' => 'payload',
			'activeRequestDataKeySubKey' => 'address'
		],
	],
	'__INSERT-IDs__' => 'address:id'
];
