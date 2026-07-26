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
				'fetchFromData' => $Constant::$YES
			],
			[
				'column' => 'is_disabled',
				'fetchFrom' => 'custom',
				'fetchFromData' => $Constant::$NO
			],
			[
				'column' => 'is_deleted',
				'fetchFrom' => 'custom',
				'fetchFromData' => $Constant::$NO
			]
		],
		'__MODE__' => 'multipleRecordFormat'
	],
	'single' => [
		'__QUERY__' => "SELECT * FROM `{$Env::$customerTable}` WHERE __WHERE__",
		'__WHERE__' => [
			[
				'column' => 'is_approved',
				'fetchFrom' => 'custom',
				'fetchFromData' => $Constant::$YES
			],
			[
				'column' => 'is_disabled',
				'fetchFrom' => 'custom',
				'fetchFromData' => $Constant::$NO
			],
			[
				'column' => 'is_deleted',
				'fetchFrom' => 'custom',
				'fetchFromData' => $Constant::$NO
			],
			[
				'column' => 'id',
				'fetchFrom' => 'routeParamArr',
				'fetchFromData' => 'id'
			]
		],
		'__MODE__' => 'singleRecordFormat'
	],
][isset($this->httpObj->httpRequestObj->session['routeParamArr']['id'])?'single':'all'];
