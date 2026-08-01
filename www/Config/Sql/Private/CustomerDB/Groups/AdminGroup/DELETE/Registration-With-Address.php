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
use Microservices\App\Env;

return [
	'__QUERY__' => "UPDATE `{$this->httpObject->httpRequestObject->activeRequestData['customerData']['customer_user_table']}` SET __SET__ WHERE __WHERE__",
	'__SET__' => [
		[
			'column' => 'customer_user_is_deleted',
			'activeRequestDataKey' => 'custom',
			'activeRequestDataKeySubKey' => Constant::$YES
		]
	],
	'__WHERE__' => [
		[
			'column' => 'customer_user_is_deleted',
			'activeRequestDataKey' => 'custom',
			'activeRequestDataKeySubKey' => Constant::$NO
		],
		[
			'column' => 'customer_user_id',
			'activeRequestDataKey' => 'routeParamArray',
			'activeRequestDataKeySubKey' => 'id',
			'dataType' => DatabaseServerDataType::$PrimaryKey
		],
	],
	'__SUB-QUERY__' => [
		'address' => [
			'__QUERY__' => 'UPDATE `address` SET __SET__ WHERE __WHERE__',
			'__SET__' => [
				[
					'column' => 'is_deleted',
					'activeRequestDataKey' => 'custom',
					'activeRequestDataKeySubKey' => Constant::$YES
				]
			],
			'__WHERE__' => [
				[
					'column' => 'is_deleted',
					'activeRequestDataKey' => 'custom',
					'activeRequestDataKeySubKey' => Constant::$NO
				],
				[
					'column' => 'id',
					'activeRequestDataKey' => 'payload',
					'activeRequestDataKeySubKey' => 'id',
					'dataType' => DatabaseServerDataType::$PrimaryKey
				],
				[
					'column' => 'customer_id',
					'activeRequestDataKey' => 'routeParamArray',
					'activeRequestDataKeySubKey' => 'id',
					'dataType' => DatabaseServerDataType::$PrimaryKey
				],
			],
		]
	],
	'__VALIDATE__' => [
		[
			'function' => 'primaryKeyExist',
			'functionArgs' => [
				'table' => ['custom', Env::$customerTable],
				'primary' => ['custom', 'customer_id'],
				'id' => ['routeParamArray', 'id']
			],
			'errorMessage' => 'Invalid registration id'
		],
	],
	'maintainHierarchy' => Constant::$TRUE,
	'idempotentWindow' => 10
];
