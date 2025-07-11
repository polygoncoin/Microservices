<?php
require __DIR__ ."/testFunctions.php"; // phpcs:ignore PEAR.Commenting.FileComment.Missing

$response = [];
echo '<pre>';

$response[] = trigger(
    method: 'GET',
    route: '/reload',
    header: [],
    payload: ''
);

// Client User
$params = [
    'username' => 'client_1_group_1_user_1',
    'password' => 'shames11'
];
$res = trigger(
    method: 'POST',
    route: '/login',
    header: [],
    payload: json_encode(value: $params)
);
if ($res) {
    $response[] = $res;
    $res = json_decode(json: $res, associative: true);
    $token = $res['Results']['Token'];
    $header = ["Authorization: Bearer {$token}"];

    $response[] = trigger(
        method: 'GET',
        route: '/routes',
        header: $header,
        payload: ''
    );

    $params = [
        [
            'name' => 'ramesh0',
            'sub' => [
                'subname' => 'ramesh1',
                'subsub' => [
                    [
                        'subsubname' => 'ramesh'
                    ],
                    [
                        'subsubname' => 'ramesh'
                    ]
                ]
            ]
        ],
        [
            'name' => 'ramesh1',
            'sub' => [
                'subname' => 'ramesh1',
                'subsub' => [
                    'subsubname' => 'ramesh'
                ]
            ]
        ]
    ];
    $response[] = trigger(
        method: 'POST',
        route: '/category',
        header: $header,
        payload: json_encode(value: $params)
    );

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
        payload: json_encode(value: $params)
    );

    $params = [
        'user_id' => 1,
        'address' => '203'
    ];
    $response[] = trigger(
        method: 'POST',
        route: '/address',
        header: $header,
        payload: json_encode(value: $params)
    );

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
        route: '/category',
        header: $header,
        payload: ''
    );
    // $response[] = trigger('GET', '/category/search', $header, $payload = '');
    $response[] = trigger(
        method: 'GET',
        route: '/category/1',
        header: $header,
        payload: ''
    );
    $response[] = trigger(
        method: 'GET',
        route: '/category&orderBy={"id":"DESC"}',
        header: $header,
        payload: ''
    );

    $response[] = trigger(
        method: 'GET',
        route: '/registration/1',
        header: $header,
        payload: ''
    );
    $response[] = trigger(
        method: 'GET',
        route: '/address/1',
        header: $header,
        payload: ''
    );
    $response[] = trigger(
        method: 'GET',
        route: '/registration-with-address/1',
        header: $header,
        payload: ''
    );

    $params = [
        'firstname' => 'Ramesh',
        'lastname' => 'Jangid',
        'email' => 'ramesh@test.com',
        'username' => 'test',
        'password' => 'shames11'
    ];
    $response[] = trigger(
        method: 'PUT',
        route: '/registration/1',
        header: $header,
        payload: json_encode(value: $params)
    );

    $params = [
        'user_id' => 1,
        'address' => '203'
    ];
    $response[] = trigger(
        method: 'PUT',
        route: '/address/1',
        header: $header,
        payload: json_encode(value: $params)
    );

    $params = [
        'firstname' => 'Ramesh',
        'lastname' => 'Jangid',
        'email' => 'ramesh_test@test.com'
    ];
    $response[] = trigger(
        method: 'PATCH',
        route: '/registration/1',
        header: $header,
        payload: json_encode(value: $params)
    );

    $params = [
        'address' => '203'
    ];
    $response[] = trigger(
        method: 'PATCH',
        route: '/address/1',
        header: $header,
        payload: json_encode(value: $params)
    );

    $response[] = trigger(
        method: 'DELETE',
        route: '/registration/1',
        header: $header,
        payload: ''
    );
    $response[] = trigger(
        method: 'DELETE',
        route: '/address/1',
        header: $header,
        payload: ''
    );

    $response[] = trigger(
        method: 'POST',
        route: '/category/config',
        header: $header,
        payload: ''
    );
}

// Admin User
$res = trigger(
    method: 'POST',
    route: '/login',
    header: [],
    payload: '{"username":"client_1_admin_1", "password":"shames11"}'
);
if ($res) {
    $response[] = $res;
    $res = json_decode(json: $res, associative: true);
    $token = $res['Results']['Token'];
    $header = ["Authorization: Bearer {$token}"];

    $response[] = trigger(
        method: 'GET',
        route: '/routes',
        header: $header,
        payload: ''
    );

    $response[] = trigger(
        method: 'DELETE',
        route: '/category/truncate',
        header: $header,
        payload: ''
    );

    $params = [
        [
            'name' => 'ramesh0',
            'sub' => [
                'subname' => 'ramesh1',
                'subsub' => [
                    [
                        'subsubname' => 'ramesh'
                    ],
                    [
                        'subsubname' => 'ramesh'
                    ]
                ]
            ]
        ],
        [
            'name' => 'ramesh1',
            'sub' => [
                'subname' => 'ramesh1',
                'subsub' => [
                    'subsubname' => 'ramesh'
                ]
            ]
        ]
    ];
    $response[] = trigger(
        method: 'POST',
        route: '/category',
        header: $header,
        payload: json_encode(value: $params)
    );

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
        payload: json_encode(value: $params)
    );

    $params = [
        'user_id' => 1,
        'address' => '203'
    ];
    $response[] = trigger(
        method: 'POST',
        route: '/address',
        header: $header,
        payload: json_encode(value: $params)
    );

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
        route: '/category',
        header: $header,
        payload: ''
    );
    // $response[] = trigger('GET', '/category/search', $header, $payload = '');
    $response[] = trigger(
        method: 'GET',
        route: '/category/1',
        header: $header,
        payload: ''
    );
    $response[] = trigger(
        method: 'GET',
        route: '/category&orderBy={"id":"DESC"}',
        header: $header,
        payload: ''
    );

    $response[] = trigger(
        method: 'GET',
        route: '/registration',
        header: $header,
        payload: ''
    );
    $response[] = trigger(
        method: 'GET',
        route: '/registration/1',
        header: $header,
        payload: ''
    );

    $response[] = trigger(
        method: 'GET',
        route: '/address',
        header: $header,
        payload: ''
    );
    $response[] = trigger(
        method: 'GET',
        route: '/address/1',
        header: $header,
        payload: ''
    );

    $response[] = trigger(
        method: 'GET',
        route: '/registration-with-address',
        header: $header,
        payload: ''
    );
    $response[] = trigger(
        method: 'GET',
        route: '/registration-with-address/1',
        header: $header,
        payload: ''
    );

    $params = [
        'firstname' => 'Ramesh',
        'lastname' => 'Jangid',
        'email' => 'ramesh@test.com',
        'username' => 'test',
        'password' => 'shames11'
    ];
    $response[] = trigger(
        method: 'PUT',
        route: '/registration/1',
        header: $header,
        payload: json_encode(value: $params)
    );

    $params = [
        'user_id' => 1,
        'address' => '203'
    ];
    $response[] = trigger(
        method: 'PUT',
        route: '/address/1',
        header: $header,
        payload: json_encode(value: $params)
    );

    $params = [
        'firstname' => 'Ramesh',
        'lastname' => 'Jangid',
        'email' => 'ramesh@test.com',
        'username' => 'test',
        'password' => 'shames11',
        'address' => [
            'id' => 1,
            'address' => 'a-203'
        ]
    ];
    $response[] = trigger(
        method: 'PUT',
        route: '/registration-with-address/1',
        header: $header,
        payload: json_encode(value: $params)
    );

    $params = [
        'firstname' => 'Ramesh',
        'lastname' => 'Jangid',
        'email' => 'ramesh_test@test.com'
    ];
    $response[] = trigger(
        method: 'PATCH',
        route: '/registration/1',
        header: $header,
        payload: json_encode(value: $params)
    );

    $params = [
        'address' => '203'
    ];
    $response[] = trigger(
        method: 'PATCH',
        route: '/address/1',
        header: $header,
        payload: json_encode(value: $params)
    );

    $params = [
        'firstname' => 'Ramesh',
        'lastname' => 'Jangid',
        'email' => 'ramesh@test.com',
        'address' => [
            'id' => 1,
            'address' => 'a-203'
        ]
    ];
    $response[] = trigger(
        method: 'PATCH',
        route: '/registration-with-address/1',
        header: $header,
        payload: json_encode(value: $params)
    );

    $response[] = trigger(
        method: 'DELETE',
        route: '/registration/1',
        header: $header,
        payload: ''
    );
    $response[] = trigger(
        method: 'DELETE',
        route: '/address/1',
        header: $header,
        payload: ''
    );

    $params = [
        'address' => [
            'user_id' => 1
        ]
    ];
    $response[] = trigger(
        method: 'DELETE',
        route: '/registration-with-address/1',
        header: $header,
        payload: json_encode(value: $params)
    );

    $response[] = trigger(
        method: 'POST',
        route: '/category/config',
        header: $header,
        payload: ''
    );
}

print_r(value: $response);
