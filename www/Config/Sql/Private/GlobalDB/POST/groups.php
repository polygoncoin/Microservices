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

use Microservices\App\DatabaseServerDataType;

return [
	'__QUERY__' => "INSERT INTO `{$this->httpObj->httpRequestObj->activeRequestCollection['userData']['customer_user_group_table']}` SET __SET__",
	'__SET__' => [
		[
			'column' => 'name',
			'activeRequestCollectionKey' => 'payload',
			'activeRequestCollectionKeySubKey' => 'name'
		],
		[
			'column' => 'customer_id',
			'activeRequestCollectionKey' => 'payload',
			'activeRequestCollectionKeySubKey' => 'customer_id',
			'dataType' => DatabaseServerDataType::$INT
		],
		[
			'column' => 'connection_id',
			'activeRequestCollectionKey' => 'payload',
			'activeRequestCollectionKeySubKey' => 'connection_id',
			'dataType' => DatabaseServerDataType::$INT
		],
		[
			'column' => 'customer_allowed_cidr',
			'activeRequestCollectionKey' => 'payload',
			'activeRequestCollectionKeySubKey' => 'allowed_cidr'
		],
		[
			'column' => 'comments',
			'activeRequestCollectionKey' => 'payload',
			'activeRequestCollectionKeySubKey' => 'comments'
		],
		[
			'column' => 'created_by',
			'activeRequestCollectionKey' => 'userData',
			'activeRequestCollectionKeySubKey' => 'id'
		],
		[
			'column' => 'created_on',
			'activeRequestCollectionKey' => 'custom',
			'activeRequestCollectionKeySubKey' => date(format: 'Y-m-d H:i:s')
		],
		[
			'column' => 'is_approved',
			'activeRequestCollectionKey' => 'custom',
			'activeRequestCollectionKeySubKey' => $Constant::$NO
		],
		[
			'column' => 'is_disabled',
			'activeRequestCollectionKey' => 'custom',
			'activeRequestCollectionKeySubKey' => $Constant::$NO
		],
		[
			'column' => 'is_deleted',
			'activeRequestCollectionKey' => 'custom',
			'activeRequestCollectionKeySubKey' => $Constant::$NO
		]
	],
	'__INSERT-IDs__' => 'group:id',
];
