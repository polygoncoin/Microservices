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
	'__QUERY__' => 'SELECT * FROM `category` WHERE __WHERE__',
	'__WHERE__' => [
		[
			'column' => 'is_deleted',
			'activeRequestCollectionKey' => 'custom',
			'activeRequestCollectionKeySubKey' => $Constant::$NO
		],
		[
			'column' => 'parent_id',
			'activeRequestCollectionKey' => 'custom',
			'activeRequestCollectionKeySubKey' => 0
		],
		[
			'column' => 'id',
			'activeRequestCollectionKey' => 'routeParamArr',
			'activeRequestCollectionKeySubKey' => 'id'
		]
	],
	'__MODE__' => 'multipleRecordFormat',
	'__SUB-QUERY__' => [
		'sub' => [
			'__QUERY__' => 'SELECT * FROM `category` WHERE __WHERE__',
			'__WHERE__' => [
				[
					'column' => 'is_deleted',
					'activeRequestCollectionKey' => 'custom',
					'activeRequestCollectionKeySubKey' => $Constant::$NO
				],
				[
					'column' => 'parent_id',
					'activeRequestCollectionKey' => 'sqlResults',
					'activeRequestCollectionKeySubKey' => 'return:id'
				],
			],
			'__MODE__' => 'multipleRecordFormat',
			'__SUB-QUERY__' => [
				'subsub' => [
					'__QUERY__' => 'SELECT * FROM `category` WHERE __WHERE__',
					'__WHERE__' => [
						[
							'column' => 'is_deleted',
							'activeRequestCollectionKey' => 'custom',
							'activeRequestCollectionKeySubKey' => $Constant::$NO
						],
						[
							'column' => 'parent_id',
							'activeRequestCollectionKey' => 'sqlResults',
							'activeRequestCollectionKeySubKey' => 'return:sub:id'
						],
					],
					'__MODE__' => 'multipleRecordFormat',
					'__SUB-QUERY__' => [
						'subsubsub' => [
							'__QUERY__' => 'SELECT * FROM `category` WHERE __WHERE__',
							'__WHERE__' => [
								[
									'column' => 'is_deleted',
									'activeRequestCollectionKey' => 'custom',
									'activeRequestCollectionKeySubKey' => $Constant::$NO
								],
								[
									'column' => 'parent_id',
									'activeRequestCollectionKey' => 'sqlResults',
									'activeRequestCollectionKeySubKey' => 'return:sub:subsub:id'
								],
							],
							'__MODE__' => 'multipleRecordFormat',
						]
					]
				]
			],
		]
	],
	'useResultSet' => $Constant::$TRUE,
];
