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
				'activeRequestDataKey' => 'payload',
				'activeRequestDataKeySubKey' => 'firstname'
			],
			[
				'column' => 'lastname',
				'activeRequestDataKey' => 'payload',
				'activeRequestDataKeySubKey' => 'lastname'
			],
			[
				'column' => 'email',
				'activeRequestDataKey' => 'payload',
				'activeRequestDataKeySubKey' => 'email'
			],
			[
				'column' => 'username',
				'activeRequestDataKey' => 'payload',
				'activeRequestDataKeySubKey' => 'username'
			],
			[
				'column' => 'password_hash',
				'activeRequestDataKey' => 'function',
				'activeRequestDataKeySubKey' => function($activeRequestData) {
					if (
						isset($activeRequestData['payload'])
						&& isset($activeRequestData['payload']['password'])
					) {
						return password_hash(
							password: $activeRequestData['payload']['password'],
							algo: PASSWORD_DEFAULT
						);
					}
				}
			]
		],
		'__WHERE__' => [
			[
				'column' => 'is_deleted',
				'activeRequestDataKey' => 'custom',
				'activeRequestDataKeySubKey' => $Constant::$NO
			],
			[
				'column' => 'id',
				'activeRequestDataKey' => 'routeParamArr',
				'activeRequestDataKeySubKey' => 'id',
				'dataType' => DatabaseServerDataType::$PrimaryKey
			]
		],
	]
);
