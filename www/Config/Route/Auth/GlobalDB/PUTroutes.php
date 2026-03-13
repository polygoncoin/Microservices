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

namespace Microservices\www\Config\Route\Auth\GlobalDB;

use Microservices\App\Constant;
use Microservices\App\DatabaseServerDataType;

return [
	'group' => [
		'{id:int}'  => [
			'dataType' => DatabaseServerDataType::$PrimaryKey,
			'__FILE__' => Constant::$AUTH_QUERIES_DIR
				. DIRECTORY_SEPARATOR . 'GlobalDB'
				. DIRECTORY_SEPARATOR . 'PUT'
				. DIRECTORY_SEPARATOR . 'groups.php',
		],
	],
	'customer' => [
		'{id:int}'  => [
			'dataType' => DatabaseServerDataType::$PrimaryKey,
			'__FILE__' => Constant::$AUTH_QUERIES_DIR
				. DIRECTORY_SEPARATOR . 'GlobalDB'
				. DIRECTORY_SEPARATOR . 'PUT'
				. DIRECTORY_SEPARATOR . 'customer.php',
		],
	],
];
