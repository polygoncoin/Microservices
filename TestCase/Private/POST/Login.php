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

use Microservices\App\Web;
use Microservices\App\Env;

$headerArr = $defaultHeaderArr;
$headerArr[] = $contentType;

$response = Web::trigger(
	homeURL: $homeURL,
	method: 'POST',
	route: '/login',
	header: $headerArr,
	payload: json_encode(value: $payload)
);

$token = null;
$sessionCookie = null;

if (isset($response['HttpResponse']['Headers']['Set-Cookie'])) {
	$sessionCookie = substr(
		string: $response['HttpResponse']['Headers']['Set-Cookie'],
		offset: 0,
		length: strpos(
			haystack: $response['HttpResponse']['Headers']['Set-Cookie'],
			needle: '; '
		)
	);
} elseif (isset($response['HttpResponse']['ResponseBody']['Results']['Token'])) {
	$token = $response['HttpResponse']['ResponseBody']['Results']['Token'];
} elseif (isset($response['HttpResponse']['ResponseBody']['Results']['SessionId'])) {
	$sessionCookie = "PHPSESSID={$response['HttpResponse']['ResponseBody']['Results']['SessionId']}";
}

return $response;
