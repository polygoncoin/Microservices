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
	'__QUERY__' => "UPDATE `{$this->httpObj->httpRequestObj->activeRequestCollection['customerData']['customer_user_table']}` SET __SET__ WHERE __WHERE__",
	'__SET__' => [
		[
			'column' => 'customer_user_contact_name',
			'activeRequestCollectionKey' => 'payload',
			'activeRequestCollectionKeySubKey' => 'firstname'
		],
		[
			'column' => 'customer_user_contact_person',
			'activeRequestCollectionKey' => 'payload',
			'activeRequestCollectionKeySubKey' => 'lastname'
		],
		[
			'column' => 'customer_user_contact_email_address',
			'activeRequestCollectionKey' => 'payload',
			'activeRequestCollectionKeySubKey' => 'email'
		],
		[
			'column' => 'customer_user_username',
			'activeRequestCollectionKey' => 'payload',
			'activeRequestCollectionKeySubKey' => 'username'
		],
		[
			'column' => 'customer_user_password_hash',
			'activeRequestCollectionKey' => 'function',
			'activeRequestCollectionKeySubKey' => function($activeRequestCollection) {
				if (
					isset($activeRequestCollection['payload'])
					&& isset($activeRequestCollection['payload']['password'])
				) {
					return password_hash(
						password: $activeRequestCollection['payload']['password'],
						algo: PASSWORD_DEFAULT
					);
				}
			}
		]
	],
	'__WHERE__' => [
		[
			'column' => 'customer_user_is_deleted',
			'activeRequestCollectionKey' => 'custom',
			'activeRequestCollectionKeySubKey' => $Constant::$NO
		],
		[
			'column' => 'customer_user_id',
			'activeRequestCollectionKey' => 'routeParamArr',
			'activeRequestCollectionKeySubKey' => 'id',
			'dataType' => DatabaseServerDataType::$PrimaryKey
		]
	],
	'__SUB-QUERY__' => [
		'address' => [
			'__QUERY__' => 'UPDATE `address` SET __SET__ WHERE __WHERE__',
			'__SET__' => [
				[
					'column' => 'address',
					'activeRequestCollectionKey' => 'payload',
					'activeRequestCollectionKeySubKey' => 'address'
				]
			],
			'__WHERE__' => [
				[
					'column' => 'is_deleted',
					'activeRequestCollectionKey' => 'custom',
					'activeRequestCollectionKeySubKey' => $Constant::$NO
				],
				[
					'column' => 'id',
					'activeRequestCollectionKey' => 'payload',
					'activeRequestCollectionKeySubKey' => 'id',
					'dataType' => DatabaseServerDataType::$PrimaryKey
				],
			],
		]
	],
	'__VALIDATE__' => [
		[
			'function' => 'primaryKeyExist',
			'functionArgs' => [
				'table' => ['custom', $this->httpObj->httpRequestObj->activeRequestCollection['customerData']['customer_user_table']],
				'primary' => ['custom', 'customer_user_id'],
				'id' => ['routeParamArr', 'id']
			],
			'errorMessage' => 'Invalid registration id'
		],
	],
	'useHierarchy' => $Constant::$TRUE,
	'idempotentWindow' => 10
];
