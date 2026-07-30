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
	// detail of data to perform task
	'__PAYLOAD__' => [
		[
			'column' => 'id',
			'activeRequestCollectionKey' => 'payload',
			'activeRequestCollectionKeySubKey' => 'payload-id-1',
		],
		[
			'column' => 'column-1',
			'activeRequestCollectionKey' => 'payload',
			'activeRequestCollectionKeySubKey' => 'payload-param-1',
		],
	],
	'__SUB-PAYLOAD__' => [
		'sub' => [
			'__PAYLOAD__' => [
				[
					'column' => 'sub-id',
					'activeRequestCollectionKey' => 'payload',
					'activeRequestCollectionKeySubKey' => 'sub-payload-id-1',
				],
				[
					'column' => 'sub-column-1',
					'activeRequestCollectionKey' => 'payload',
					'activeRequestCollectionKeySubKey' => 'sub-payload-param-1',
				],
			],
		]
	],
	'__PRE-SQL-HOOKS__' => [
		'Hook_Example',
	],

	'useHierarchy' => $Constant::$TRUE
];
