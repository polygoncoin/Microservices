<?php
require __DIR__ ."/testFunctions.php"; // phpcs:ignore PEAR.Commenting.FileComment.Missing

$response = [];
$header = [];

$params = [
    'Paylaod' => [
        'firstname' => 'Ramesh1',
        'lastname' => 'Jangid',
        'email' => 'ramesh@test.com',
        'username' => 'test',
        'password' => 'shames11',
        'address' => [
            'address' => 'A-203'
        ]
    ]
];

$payload = '<?xml version="1.0" encoding="UTF-8" ?>';
genXmlPayload(params: $params, payload: $payload);

$response = trigger(
    method: 'POST',
    route: '/registration-with-address&inputRepresentation=Xml&outputRepresentation=Xml',
    header: $header,
    payload: $payload
);

header(header: "Content-type: text/xml");
echo $response;
