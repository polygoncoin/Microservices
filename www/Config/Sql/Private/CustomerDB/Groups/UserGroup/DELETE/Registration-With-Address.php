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
	'__QUERY__' => "UPDATE `{$this->httpObj->httpRequestObj->session['customerData']['customer_user_table']}` SET __SET__ WHERE __WHERE__",
	'__SET__' => [
		[
			'column' => 'customer_user_is_deleted',
			'fetchFrom' => 'custom',
			'fetchFromData' => $Constant::$YES
		]
	],
	'__WHERE__' => [
		[
			'column' => 'customer_user_is_deleted',
			'fetchFrom' => 'custom',
			'fetchFromData' => $Constant::$NO
		],
		[
			'column' => 'customer_user_id',
			'fetchFrom' => 'routeParamArr',
			'fetchFromData' => 'id',
			'dataType' => DatabaseServerDataType::$PrimaryKey
		]
	],
	'__SUB-QUERY__' => [
		'address' => [
			'__QUERY__' => 'UPDATE `address` SET __SET__ WHERE __WHERE__',
			'__SET__' => [
				[
					'column' => 'is_deleted',
					'fetchFrom' => 'custom',
					'fetchFromData' => $Constant::$YES
				]
			],
			'__WHERE__' => [
				[
					'column' => 'is_deleted',
					'fetchFrom' => 'custom',
					'fetchFromData' => $Constant::$NO
				],
				[
					'column' => 'id',
					'fetchFrom' => 'payload',
					'fetchFromData' => 'id',
					'dataType' => DatabaseServerDataType::$PrimaryKey
				],
				[
					'column' => 'customer_id',
					'fetchFrom' => 'routeParamArr',
					'fetchFromData' => 'id',
					'dataType' => DatabaseServerDataType::$PrimaryKey
				],
			],
		]
	],
	'__VALIDATE__' => [
		[
			'function' => 'primaryKeyExist',
			'functionArgs' => [
				'table' => ['custom', $this->httpObj->httpRequestObj->session['customerData']['customer_user_table']],
				'primary' => ['custom', 'customer_user_id'],
				'id' => ['routeParamArr', 'id']
			],
			'errorMessage' => 'Invalid registration id'
		],
	],
	'useHierarchy' => $Constant::$TRUE,
	'idempotentWindow' => 10
];
