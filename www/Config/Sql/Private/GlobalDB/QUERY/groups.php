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

return [
	'all' => [
		'__QUERY__' => "SELECT * FROM `{$this->httpObj->httpRequestObj->activeRequestData['userData']['customer_user_group_table']}` WHERE __WHERE__ ORDER BY id ASC",
		'__WHERE__' => [
			[
				'column' => 'is_approved',
				'activeRequestDataKey' => 'custom',
				'activeRequestDataKeySubKey' => $Constant::$YES
			],
			[
				'column' => 'is_disabled',
				'activeRequestDataKey' => 'custom',
				'activeRequestDataKeySubKey' => $Constant::$NO
			],
			[
				'column' => 'is_deleted',
				'activeRequestDataKey' => 'custom',
				'activeRequestDataKeySubKey' => $Constant::$NO
			],
		],
		'__MODE__' => 'multipleRecordFormat'
	],
	'single' => [
		'__QUERY__' => "SELECT * FROM `{$this->httpObj->httpRequestObj->activeRequestData['userData']['customer_user_group_table']}` WHERE __WHERE__",
		'__WHERE__' => [
			[
				'column' => 'is_approved',
				'activeRequestDataKey' => 'custom',
				'activeRequestDataKeySubKey' => $Constant::$YES
			],
			[
				'column' => 'is_disabled',
				'activeRequestDataKey' => 'custom',
				'activeRequestDataKeySubKey' => $Constant::$NO
			],
			[
				'column' => 'is_deleted',
				'activeRequestDataKey' => 'custom',
				'activeRequestDataKeySubKey' => $Constant::$NO
			],
			[
				'column' => 'id',
				'activeRequestDataKey' => 'routeParamArr',
				'activeRequestDataKeySubKey' => 'id'
			],
		],
		'__MODE__' => 'singleRecordFormat'
	]
][isset($this->httpObj->httpRequestObj->activeRequestData['routeParamArr']['id'])?'single':'all'];
