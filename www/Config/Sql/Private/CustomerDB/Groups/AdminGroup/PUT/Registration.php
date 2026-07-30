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

return array_merge(
	require $this->httpObj->httpRequestObj->QUERIES_DIR
		. DIRECTORY_SEPARATOR . 'CustomerDB'
		. DIRECTORY_SEPARATOR . 'Common'
		. DIRECTORY_SEPARATOR . 'Registration.php',
	[
		'__SET__' => [
			[
				'column' => 'firstname',
				'activeRequestCollectionKey' => 'payload',
				'activeRequestCollectionKeySubKey' => 'firstname'
			],
			[
				'column' => 'lastname',
				'activeRequestCollectionKey' => 'payload',
				'activeRequestCollectionKeySubKey' => 'lastname'
			],
			[
				'column' => 'email',
				'activeRequestCollectionKey' => 'payload',
				'activeRequestCollectionKeySubKey' => 'email'
			],
			[
				'column' => 'username',
				'activeRequestCollectionKey' => 'payload',
				'activeRequestCollectionKeySubKey' => 'username'
			],
			[
				'column' => 'password_hash',
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
				'column' => 'is_deleted',
				'activeRequestCollectionKey' => 'custom',
				'activeRequestCollectionKeySubKey' => $Constant::$NO
			],
			[
				'column' => 'id',
				'activeRequestCollectionKey' => 'routeParamArr',
				'activeRequestCollectionKeySubKey' => 'id',
				'dataType' => DatabaseServerDataType::$PrimaryKey
			]
		],
	]
);
