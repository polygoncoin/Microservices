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
	'__QUERY__' => "UPDATE `{$this->httpObject->httpRequestObject->activeRequestData['userData']['customer_user_group_table']}` SET __SET__ WHERE __WHERE__",
	'__SET__' => [
		[
			'column' => 'name',
			'activeRequestDataKey' => 'payload',
			'activeRequestDataKeySubKey' => 'name'
		],
		[
			'column' => 'customer_id',
			'activeRequestDataKey' => 'payload',
			'activeRequestDataKeySubKey' => 'customer_id',
			'dataType' => DatabaseServerDataType::$INT
		],
		[
			'column' => 'connection_id',
			'activeRequestDataKey' => 'payload',
			'activeRequestDataKeySubKey' => 'connection_id',
			'dataType' => DatabaseServerDataType::$INT
		],
		[
			'column' => 'customer_allowed_cidr',
			'activeRequestDataKey' => 'payload',
			'activeRequestDataKeySubKey' => 'allowed_cidr'
		],
		[
			'column' => 'comments',
			'activeRequestDataKey' => 'payload',
			'activeRequestDataKeySubKey' => 'comments'
		],
		[
			'column' => 'updated_by',
			'activeRequestDataKey' => 'userData',
			'activeRequestDataKeySubKey' => 'id'
		],
		[
			'column' => 'updated_on',
			'activeRequestDataKey' => 'custom',
			'activeRequestDataKeySubKey' => date(format: 'Y-m-d H:i:s')
		]
	],
	'__WHERE__' => [
		[
			'column' => 'is_approved',
			'activeRequestDataKey' => 'custom',
			'activeRequestDataKeySubKey' => Constant::$YES
		],
		[
			'column' => 'is_disabled',
			'activeRequestDataKey' => 'custom',
			'activeRequestDataKeySubKey' => Constant::$NO
		],
		[
			'column' => 'is_deleted',
			'activeRequestDataKey' => 'custom',
			'activeRequestDataKeySubKey' => Constant::$NO
		],
		[
			'column' => 'id',
			'activeRequestDataKey' => 'routeParamArray',
			'activeRequestDataKeySubKey' => 'id',
			'dataType' => DatabaseServerDataType::$INT
		]
	],
	'__VALIDATE__' => [
		[
			'function' => 'primaryKeyExist',
			'functionArgs' => [
				'table' => ['custom', $this->httpObject->httpRequestObject->activeRequestData['userData']['customer_user_group_table']],
				'primary' => ['custom', 'id'],
				'id' => ['payload', 'id', DatabaseServerDataType::$INT]
			],
			'errorMessage' => 'Invalid Group Id'
		],
	]
];
