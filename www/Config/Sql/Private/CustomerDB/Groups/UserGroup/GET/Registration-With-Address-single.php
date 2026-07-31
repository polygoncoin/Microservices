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

return [
	'__QUERY__' => "SELECT * FROM `{$this->httpObject->httpRequestObject->activeRequestData['customerData']['customer_user_table']}` WHERE __WHERE__",
	'__WHERE__' => [
		[
			'column' => 'is_deleted',
			'activeRequestDataKey' => 'custom',
			'activeRequestDataKeySubKey' => Constant::$NO
		],
		[
			'column' => 'id',
			'activeRequestDataKey' => 'routeParamArray',
			'activeRequestDataKeySubKey' => 'id'
		]
	],
	'__MODE__' => 'singleRecordFormat',
	'__SUB-QUERY__' => [
		'address' => [
			'__QUERY__' => 'SELECT * FROM `address` WHERE __WHERE__',
			'__WHERE__' => [
				[
					'column' => 'is_deleted',
					'activeRequestDataKey' => 'custom',
					'activeRequestDataKeySubKey' => Constant::$NO
				],
				[
					'column' => 'customer_id',
					'activeRequestDataKey' => 'sqlResults',
					'activeRequestDataKeySubKey' => 'return:id'
				],
			],
			'__MODE__' => 'multipleRecordFormat',
		]
	],
	'useResultSet' => Constant::$TRUE
];
