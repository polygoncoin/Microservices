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
	'__QUERY__' => "INSERT INTO `{$this->http->req->s['cDetails']['usersTable']}` SET __SET__",
	'__SET__' => [
		[
			'column' => 'customer_id',
			'fetchFrom' => 'cDetails',
			'fetchFromDetails' => 'id'
		],
		[
			'column' => 'firstname',
			'fetchFrom' => 'payload',
			'fetchFromDetails' => 'firstname'
		],
		[
			'column' => 'lastname',
			'fetchFrom' => 'payload',
			'fetchFromDetails' => 'lastname'
		],
		[
			'column' => 'email',
			'fetchFrom' => 'payload',
			'fetchFromDetails' => 'email'
		],
		[
			'column' => 'username',
			'fetchFrom' => 'payload',
			'fetchFromDetails' => 'username'
		],
		[
			'column' => 'password_hash',
			'fetchFrom' => 'function',
			'fetchFromDetails' => function($session): string {
				return password_hash(
					password: $session['payload']['password'],
					algo: PASSWORD_DEFAULT
				);
			}
		],
		[
			'column' => 'allowed_cidr',
			'fetchFrom' => 'custom',
			'fetchFromDetails' => '0.0.0.0/0'
		],
		[
			'column' => 'group_id',
			'fetchFrom' => 'custom',
			'fetchFromDetails' => '1'
		],
	],
	'__INSERT-IDs__' => 'registration:id',
	'__PAYLOAD-TYPE__' => 'Object',
	'idempotentWindow' => 10
];
