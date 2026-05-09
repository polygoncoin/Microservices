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
	'__QUERY__' => "INSERT INTO `{$Env::$groupsTable}` SET __SET__",
	'__SET__' => [
		[
			'column' => 'name',
			'fetchFrom' => 'payload',
			'fetchFromDetails' => 'name'
		],
		[
			'column' => 'customer_id',
			'fetchFrom' => 'payload',
			'fetchFromDetails' => 'customer_id',
			'dataType' => DatabaseServerDataType::$INT
		],
		[
			'column' => 'connection_id',
			'fetchFrom' => 'payload',
			'fetchFromDetails' => 'connection_id',
			'dataType' => DatabaseServerDataType::$INT
		],
		[
			'column' => 'allowed_cidr',
			'fetchFrom' => 'payload',
			'fetchFromDetails' => 'allowed_cidr'
		],
		[
			'column' => 'comments',
			'fetchFrom' => 'payload',
			'fetchFromDetails' => 'comments'
		],
		[
			'column' => 'created_by',
			'fetchFrom' => 'uDetails',
			'fetchFromDetails' => 'id'
		],
		[
			'column' => 'created_on',
			'fetchFrom' => 'custom',
			'fetchFromDetails' => date(format: 'Y-m-d H:i:s')
		],
		[
			'column' => 'is_approved',
			'fetchFrom' => 'custom',
			'fetchFromDetails' => 'No'
		],
		[
			'column' => 'is_disabled',
			'fetchFrom' => 'custom',
			'fetchFromDetails' => 'No'
		],
		[
			'column' => 'is_deleted',
			'fetchFrom' => 'custom',
			'fetchFromDetails' => 'No'
		]
	],
	'__INSERT-IDs__' => 'group:id',
];
