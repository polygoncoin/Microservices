<?php

/**
 * API Route config
 * php version 8.3
 *
 * @category  API_Route_Config
 * @package   Microservices
 * @author    Ramesh N. Jangid (Sharma) <polygon.co.in@gmail.com>
 * @copyright © 2026 Ramesh N. Jangid (Sharma)
 * @license   MIT https://opensource.org/license/mit
 * @link      https://github.com/polygoncoin/Microservices
 * @since     Class available since Release 1.0.0
 */

namespace Microservices\www\Config\Routes\Auth\GlobalDB;

use Microservices\App\Constants;
use Microservices\App\DatabaseDataTypes;

return [
	'group' => [
		'__FILE__' => Constants::$AUTH_QUERIES_DIR
			. DIRECTORY_SEPARATOR . 'GlobalDB'
			. DIRECTORY_SEPARATOR . 'GET'
			. DIRECTORY_SEPARATOR . 'groups.php',
		'{id:int}'  => [
			'dataType' => DatabaseDataTypes::$PrimaryKey,
			'__FILE__' => Constants::$AUTH_QUERIES_DIR
				. DIRECTORY_SEPARATOR . 'GlobalDB'
				. DIRECTORY_SEPARATOR . 'GET'
				. DIRECTORY_SEPARATOR . 'groups.php',
		],
	],
	'customer' => [
		'__FILE__' => Constants::$AUTH_QUERIES_DIR
			. DIRECTORY_SEPARATOR . 'GlobalDB'
			. DIRECTORY_SEPARATOR . 'GET'
			. DIRECTORY_SEPARATOR . 'customer.php',
		'{id:int}'  => [
			'dataType' => DatabaseDataTypes::$PrimaryKey,
			'__FILE__' => Constants::$AUTH_QUERIES_DIR
				. DIRECTORY_SEPARATOR . 'GlobalDB'
				. DIRECTORY_SEPARATOR . 'GET'
				. DIRECTORY_SEPARATOR . 'customer.php',
		],
	]
];
