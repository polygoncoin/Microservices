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
	'__QUERY__' => 'INSERT INTO `category` SET __SET__',
	'__SET__' => [
		[
			'column' => 'name',
			'activeRequestCollectionKey' => 'payload',
			'activeRequestCollectionKeySubKey' => 'name'
		],
		[
			'column' => 'parent_id',
			'activeRequestCollectionKey' => 'custom',
			'activeRequestCollectionKeySubKey' => 0
		],
	],
	'__INSERT-IDs__' => 'category:id',
	'__SUB-QUERY__' => [
		'sub' => [
			'__QUERY__' => 'INSERT INTO `category` SET __SET__',
			'__SET__' => [
				[
					'column' => 'name',
					'activeRequestCollectionKey' => 'payload',
					'activeRequestCollectionKeySubKey' => 'subname'
				],
				[
					'column' => 'parent_id',
					'activeRequestCollectionKey' => '__INSERT-IDs__',
					'activeRequestCollectionKeySubKey' => 'category:id'
				],
			],
			'__INSERT-IDs__' => 'sub:id',
			'__SUB-QUERY__' => [
				'subsub' => [
					'__QUERY__' => 'INSERT INTO `category` SET __SET__',
					'__SET__' => [
						[
							'column' => 'name',
							'activeRequestCollectionKey' => 'payload',
							'activeRequestCollectionKeySubKey' => 'subsubname'
						],
						[
							'column' => 'parent_id',
							'activeRequestCollectionKey' => '__INSERT-IDs__',
							'activeRequestCollectionKeySubKey' => 'sub:id'
						],
					],
					'__INSERT-IDs__' => 'subsub:id',
				]
			]
		]
	],
	'useHierarchy' => $Constant::$TRUE,
	'affectedQueryCacheKeyArr' => [
		$this->httpObj->httpRequestObj->activeRequestCollection['customerData']['customer_id'] . ':category',
		$this->httpObj->httpRequestObj->activeRequestCollection['customerData']['customer_id'] . ':category1'
	]
];
