<?php

/**
 * Test Case
 * php version 8.3
 *
 * @category  Test Case
 * @package   Microservices
 * @author    Ramesh N. Jangid (Sharma) <polygon.co.in@gmail.com>
 * @copyright © 2026 Ramesh N. Jangid (Sharma)
 * @license   MIT https://opensource.org/license/mit
 * @link      https://github.com/polygoncoin/Microservices
 * @since     Class available since Release 1.0.0
 */

namespace Microservices\TestCase;

use Microservices\App\Constant;
use Microservices\App\Env;
use Microservices\App\Web;

$headerArr = $defaultHeaderArr;
$headerArr[] = $contentType;
$proceed = false;

if (
	isset($token)
	&& $token !== Constant::$NULL
) {
	$headerArr[] = "Authorization: Bearer {$token}";
	$proceed = true;
}
if (
	isset($sessionCookie)
	&& $sessionCookie !== Constant::$NULL
) {
	$headerArr[] = "Cookie: {$sessionCookie}";
	$proceed = true;
}

if (isset($proceed)) {
	$paramArr = [
		'firstname' => 'Ramesh',
		'lastname' => 'Jangid',
		'email' => 'ramesh_test@test.com'
	];

	return Web::trigger(
		homeURL: $homeURL,
		method: 'PATCH',
		route: '/registration/1',
		header: $headerArr,
		payload: json_encode(value: $paramArr)
	);
}
