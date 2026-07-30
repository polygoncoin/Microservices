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
	'__QUERY__' => 'INSERT INTO `address` SET __SET__',
	'__SET__' => [
		[
			'column' => 'customer_id',
			'activeRequestCollectionKey' => 'customerData',
			'activeRequestCollectionKeySubKey' => 'customer_id'
		],
		[
			'column' => 'user_id',
			'activeRequestCollectionKey' => 'payload',
			'activeRequestCollectionKeySubKey' => 'id',
			'dataType' => DatabaseServerDataType::$INT
		],
		[
			'column' => 'address',
			'activeRequestCollectionKey' => 'payload',
			'activeRequestCollectionKeySubKey' => 'address'
		],
	],
	'__INSERT-IDs__' => 'address:id',
	// '__TRIGGERS__' => [
	//     [
	//         '__ROUTE__' => [
	//             [
	//                 'activeRequestCollectionKey' => 'custom',
	//                 'activeRequestCollectionKeySubKey' => 'address'
	//             ],
	//             [
	//                 'activeRequestCollectionKey' => '__INSERT-IDs__',
	//                 'activeRequestCollectionKeySubKey' => 'address:id'
	//             ]
	//         ],
	//         '__QUERY-STRING__' => [
	//             [
	//                 'column' => 'param-1',
	//                 'activeRequestCollectionKey' => 'custom',
	//                 'activeRequestCollectionKeySubKey' => 'address'
	//             ],
	//             [
	//                 'column' => 'param-2',
	//                 'activeRequestCollectionKey' => '__INSERT-IDs__',
	//                 'activeRequestCollectionKeySubKey' => 'address:id'
	//             ]
	//         ],
	//         '__METHOD__' => 'PATCH',
	//         '__PAYLOAD__' => [
	//             [
	//                 'column' => 'address',
	//                 'activeRequestCollectionKey' => 'custom',
	//                 'activeRequestCollectionKeySubKey' => 'updated-address'
	//             ]
	//         ]
	//     ]
	// ],
	'isTransaction' => $Constant::$FALSE
];
