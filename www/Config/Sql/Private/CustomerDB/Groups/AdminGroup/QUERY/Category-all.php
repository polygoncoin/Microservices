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
	'countQuery' => 'SELECT count(1) as `count` FROM `category` WHERE __WHERE__',
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
	'fetchDbMode' => 'Master',
	'queryCacheKey' => $this->httpObj->httpRequestObj->activeRequestCollection['customerData']['customer_id'] . ':category'
];
