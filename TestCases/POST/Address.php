<?php

/**
 * TestCases
 * php version 8.3
 *
 * @category  TestCases
 * @package   Microservices
 * @author    Ramesh N Jangid <polygon.co.in@gmail.com>
 * @copyright © 2026 Ramesh N. Jangid (Sharma)
 * @license   MIT https://opensource.org/license/mit
 * @link      https://github.com/polygoncoin/Microservices
 * @since     Class available since Release 1.0.0
 */

namespace Microservices\TestCases;

use Microservices\App\Web;

$header = $defaultHeaders;
$header[] = $contentType;
if (isset($token)) {
    $header[] = "Authorization: Bearer {$token}";

    $params = [
        'user_id' => 1,
        'address' => '203'
    ];

    return Web::trigger(
        homeURL: $homeURL,
        method: 'POST',
        route: '/address',
        header: $header,
        payload: json_encode(value: $params)
    );
}
