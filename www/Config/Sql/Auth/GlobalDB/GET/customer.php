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
		'countQuery' => "SELECT count(1) as `count` FROM `{$Env::$customerTable}` WHERE __WHERE__",
		'__QUERY__' => "SELECT * FROM `{$Env::$customerTable}` WHERE __WHERE__ ORDER BY id ASC",
		'__WHERE__' => [
			[
				'column' => 'is_approved',
				'fetchFrom' => 'custom',
				'fetchFromDetails' => 'Yes'
			],
			[
				'column' => 'is_disabled',
				'fetchFrom' => 'custom',
				'fetchFromDetails' => 'No'
			],
			[
				'column' => 'is_deleted',
				'fetchFrom' => 'custom',
				'fetchFromDetails' => 'No'
			]
		],
		'__MODE__' => 'multipleRowFormat'
	],
	'single' => [
		'__QUERY__' => "SELECT * FROM `{$Env::$customerTable}` WHERE __WHERE__",
		'__WHERE__' => [
			[
				'column' => 'is_approved',
				'fetchFrom' => 'custom',
				'fetchFromDetails' => 'Yes'
			],
			[
				'column' => 'is_disabled',
				'fetchFrom' => 'custom',
				'fetchFromDetails' => 'No'
			],
			[
				'column' => 'is_deleted',
				'fetchFrom' => 'custom',
				'fetchFromDetails' => 'No'
			],
			[
				'column' => 'id',
				'fetchFrom' => 'routeParams',
				'fetchFromDetails' => 'id'
			]
		],
		'__MODE__' => 'singleRowFormat'
	],
][isset($this->http->req->s['routeParams']['id'])?'single':'all'];
