<?php
require __DIR__ ."/testFunctions.php"; // phpcs:ignore PEAR.Commenting.FileComment.Missing

$response = [];
$header = [];
echo '<pre>';

$params = [
    'firstname' => 'Ramesh',
    'lastname' => 'Jangid',
    'email' => 'ramesh@test.com',
    'username' => 'test',
    'password' => 'shames11'
];
$response[] = trigger(
    method: 'POST',
    route: '/registration',
    header: $header,
    payload: json_encode(value: $params));

$params = [
    'firstname' => 'Ramesh',
    'lastname' => 'Jangid',
    'email' => 'ramesh@test.com',
    'username' => 'test',
    'password' => 'shames11',
    'address' => [
        'address' => 'A-203'
    ]
];
$response[] = trigger(
    method: 'POST',
    route: '/registration-with-address',
    header: $header,
    payload: json_encode(value: $params)
);

$response[] = trigger(
    method: 'GET',
    route: '/category/1',
    header: $header,
    payload: ''
);
// $response[] = trigger('GET', '/category/search', $header, $payload = '');
$response[] = trigger(
    method: 'GET',
    route: '/category',
    header: $header,
    payload: ''
);
$response[] = trigger(
    method: 'GET',
    route: '/category&orderBy={"id":"DESC"}',
    header: $header,
    payload: ''
);

print_r(value: $response);
