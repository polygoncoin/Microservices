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
	'__QUERY__' => "INSERT INTO `{$this->httpObj->httpRequestObj->activeRequestCollection['customerData']['customer_user_table']}` SET __SET__",
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
		],
		[
			'column' => 'customer_user_allowed_cidr',
			'activeRequestCollectionKey' => 'custom',
			'activeRequestCollectionKeySubKey' => '0.0.0.0/0'
		],
		[
			'column' => 'customer_user_group_id',
			'activeRequestCollectionKey' => 'custom',
			'activeRequestCollectionKeySubKey' => '1'
		],
	],
	'__INSERT-IDs__' => 'registration:id',
	'__PAYLOAD-TYPE__' => 'Object',
	'idempotentWindow' => 10
];
