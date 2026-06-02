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
	'__QUERY__' => "INSERT INTO `{$this->http->req->s['customerData']['customer_user_table']}` SET __SET__",
	'__SET__' => [
		[
			'column' => 'customer_user_contact_name',
			'fetchFrom' => 'payload',
			'fetchFromData' => 'firstname'],
		[
			'column' => 'customer_user_contact_person',
			'fetchFrom' => 'payload',
			'fetchFromData' => 'lastname'],
		[
			'column' => 'customer_user_contact_email_address',
			'fetchFrom' => 'payload',
			'fetchFromData' => 'email'],
		[
			'column' => 'customer_user_username',
			'fetchFrom' => 'payload',
			'fetchFromData' => 'username'],
		[
			'column' => 'customer_user_password_hash',
			'fetchFrom' => 'function',
			'fetchFromData' => function($session) {
				if (
					isset($session['payload'])
					&& isset($session['payload']['password'])
				) {
					return password_hash(
						password: $session['payload']['password'],
						algo: PASSWORD_DEFAULT
					);
				}
			}
		],
		[
			'column' => 'customer_user_allowed_cidr',
			'fetchFrom' => 'custom',
			'fetchFromData' => '0.0.0.0/0'],
		[
			'column' => 'customer_user_group_id',
			'fetchFrom' => 'custom',
			'fetchFromData' => '1'
		],
	],
	'__INSERT-IDs__' => 'registration:id',
	'idempotentWindow' => 10
];
