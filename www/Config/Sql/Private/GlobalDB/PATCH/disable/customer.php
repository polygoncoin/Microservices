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
	'__QUERY__' => "UPDATE `{$Env::$customerTable}` SET __SET__ WHERE __WHERE__",
	'__SET__' => [
		[
			'column' => 'is_disabled',
			'activeRequestCollectionKey' => 'custom',
			'activeRequestCollectionKeySubKey' => $Constant::$YES
		],
		[
			'column' => 'updated_by',
			'activeRequestCollectionKey' => 'userData',
			'activeRequestCollectionKeySubKey' => 'id'
		],
		[
			'column' => 'updated_on',
			'activeRequestCollectionKey' => 'custom',
			'activeRequestCollectionKeySubKey' => date(format: 'Y-m-d H:i:s')
		]
	],
	'__WHERE__' => [
		[
			'column' => 'is_disabled',
			'activeRequestCollectionKey' => 'custom',
			'activeRequestCollectionKeySubKey' => $Constant::$NO
		],
		[
			'column' => 'is_deleted',
			'activeRequestCollectionKey' => 'custom',
			'activeRequestCollectionKeySubKey' => $Constant::$NO
		],
		[
			'column' => 'id',
			'activeRequestCollectionKey' => 'payload',
			'activeRequestCollectionKeySubKey' => 'id',
			'dataType' => DatabaseServerDataType::$INT
		]
	],
	'__VALIDATE__' => [
		[
			'function' => 'primaryKeyExist',
			'functionArgs' => [
				'table' => ['custom', $Env::$customerTable],
				'primary' => ['custom', 'customer_id'],
				'id' => ['payload', 'id', DatabaseServerDataType::$INT]
			],
			'errorMessage' => 'Invalid Customer Id'
		],
		[
			'function' => '_checkColumnValueExist',
			'functionArgs' => [
				'table' => ['custom', $Env::$customerTable],
				'column' => ['custom', 'is_deleted'],
				'columnValue' => ['custom', $Constant::$NO],
				'primary' => ['custom', 'customer_id'],
				'id' => ['payload', 'id', DatabaseServerDataType::$INT],
			],
			'errorMessage' => 'Record is deleted'
		],
		[
			'function' => '_checkColumnValueExist',
			'functionArgs' => [
				'table' => ['custom', $Env::$customerTable],
				'column' => ['custom', 'is_disabled'],
				'columnValue' => ['custom', $Constant::$NO],
				'primary' => ['custom', 'customer_id'],
				'id' => ['payload', 'id', DatabaseServerDataType::$INT],
			],
			'errorMessage' => 'Record is already disabled'
		]
	]
];
