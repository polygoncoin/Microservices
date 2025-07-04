<?php

function getCurlConfig($method, $route, $header = [], $payload = '')
{
    $homeURL = 'http://api.client001.localhost/Microservices/public_html/index.php';

    $curlConfig[CURLOPT_URL] = "{$homeURL}?r={$route}&inputDataRepresentation=Json&outputDataRepresentation=XJson";
    $curlConfig[CURLOPT_HTTPHEADER] = $header;
    $curlConfig[CURLOPT_HTTPHEADER][] = 'X-API-Version: v1.0.0';
    $curlConfig[CURLOPT_HTTPHEADER][] = 'Cache-Control: no-cache';
    $curlConfig[CURLOPT_HEADER] = 1;

    switch ($method) {
        case 'GET':
            break;
        case 'POST':
            $curlConfig[CURLOPT_HTTPHEADER][] = 'Content-Type: text/plain; charset=utf-8';
            $curlConfig[CURLOPT_POST] = true;
            $curlConfig[CURLOPT_POSTFIELDS] = $payload;
            break;
        case 'PUT':
            $curlConfig[CURLOPT_HTTPHEADER][] = 'Content-Type: text/plain; charset=utf-8';
            $curlConfig[CURLOPT_CUSTOMREQUEST] = 'PUT';
            $curlConfig[CURLOPT_POSTFIELDS] = $payload;
            break;
        case 'PATCH':
            $curlConfig[CURLOPT_HTTPHEADER][] = 'Content-Type: text/plain; charset=utf-8';
            $curlConfig[CURLOPT_CUSTOMREQUEST] = 'PATCH';
            $curlConfig[CURLOPT_POSTFIELDS] = $payload;
            break;
        case 'DELETE':
            $curlConfig[CURLOPT_HTTPHEADER][] = 'Content-Type: text/plain; charset=utf-8';
            $curlConfig[CURLOPT_CUSTOMREQUEST] = 'DELETE';
            $curlConfig[CURLOPT_POSTFIELDS] = $payload;
            break;
    }
    $curlConfig[CURLOPT_RETURNTRANSFER] = true;

    return $curlConfig;
}

function trigger($method, $route, $header = [], $payload = '')
{
    $curl = curl_init();
    $curlConfig = getCurlConfig($method, $route, $header, $payload);
    curl_setopt_array($curl, $curlConfig);
    $curlResponse = curl_exec($curl);

    $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    $contentType = curl_getinfo($curl, CURLINFO_CONTENT_TYPE);

    $headerSize = curl_getinfo($curl, CURLINFO_HEADER_SIZE);
    $responseHeaders = http_parse_headers(substr($curlResponse, 0, $headerSize));
    $responseBody = substr($curlResponse, $headerSize);

    $error = curl_error($curl);
    curl_close($curl);

    if ($error) {
        $response = ['cURL Error #:' . $error];
    } else {
        $response = json_decode($responseBody, true);
        if (
            !empty($response)
            && (
                (isset($response['Status']) && $response['Status'] == 200)
                || (isset($response['Results']['Status']) && $response['Results']['Status'] == 200)
            )
        ) {
            echo 'Sucess:'.$method.$route . PHP_EOL . PHP_EOL;
        } else {
            echo 'Failed:'.$method.$route . PHP_EOL;
            echo 'O/P:' . $responseBody . PHP_EOL . PHP_EOL;
            $response = false;
        }
    }

    return [
        'httpCode' => $httpCode,
        'contentType' => $contentType,
        'headers' => $responseHeaders,
        'body' => $response
    ];
}

if (!function_exists('http_parse_headers')) {
    function http_parse_headers($raw_headers) {
        $headers = array();
        $key = '';

        foreach(explode("\n", $raw_headers) as $i => $h) {
            $h = explode(':', $h, 2);

            if (isset($h[1])) {
                if (!isset($headers[$h[0]]))
                    $headers[$h[0]] = trim($h[1]);
                elseif (is_array($headers[$h[0]])) {
                    $headers[$h[0]] = array_merge($headers[$h[0]], array(trim($h[1])));
                }
                else {
                    $headers[$h[0]] = array_merge(array($headers[$h[0]]), array(trim($h[1])));
                }

                $key = $h[0];
            }
            else {
                if (substr($h[0], 0, 1) == "\t")
                    $headers[$key] .= "\r\n\t".trim($h[0]);
                elseif (!$key)
                    $headers[0] = trim($h[0]);
            }
        }

        return $headers;
    }
}

$response = [];
echo '<pre>';

$response[] = trigger('GET', '/reload', [], $payload = '');

// Client User
$params = [
    'username' => 'client_1_group_1_user_1',
    'password' => 'shames11'
];
$res = trigger('POST', '/login', [], $payload = json_encode($params));
if ($res) {
    $response[] = $res;
    $token = $res['body']['Results']['Token'];
    $header = ["Authorization: Bearer {$token}"];

    $response[] = trigger('GET', '/routes', $header, $payload = '');

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
    $response[] = trigger('POST', '/category', $header, $payload = json_encode($params));

    $params = [
        'firstname' => 'Ramesh',
        'lastname' => 'Jangid',
        'email' => 'ramesh@test.com',
        'username' => 'test',
        'password' => 'shames11'
    ];
    $response[] = trigger('POST', '/registration', $header, $payload = json_encode($params));

    $params = [
        'user_id' => 1,
        'address' => '203'
    ];
    $response[] = trigger('POST', '/address', $header, $payload = json_encode($params));

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
    $response[] = trigger('POST', '/registration-with-address', $header, $payload = json_encode($params));

    $response[] = trigger('GET', '/category', $header, $payload = '');
    // $response[] = trigger('GET', '/category/search', $header, $payload = '');
    $response[] = trigger('GET', '/category/1', $header, $payload = '');
    $response[] = trigger('GET', '/category&orderBy={"id":"DESC"}', $header, $payload = '');

    $response[] = trigger('GET', '/registration/1', $header, $payload = '');
    $response[] = trigger('GET', '/address/1', $header, $payload = '');
    $response[] = trigger('GET', '/registration-with-address/1', $header, $payload = '');

    $params = [
        'firstname' => 'Ramesh',
        'lastname' => 'Jangid',
        'email' => 'ramesh@test.com',
        'username' => 'test',
        'password' => 'shames11'
    ];
    $response[] = trigger('PUT', '/registration/1', $header, $payload = json_encode($params));

    $params = [
        'user_id' => 1,
        'address' => '203'
    ];
    $response[] = trigger('PUT', '/address/1', $header, $payload = json_encode($params));

    $params = [
        'firstname' => 'Ramesh',
        'lastname' => 'Jangid',
        'email' => 'ramesh_test@test.com'
    ];
    $response[] = trigger('PATCH', '/registration/1', $header, $payload = json_encode($params));

    $params = [
        'address' => '203'
    ];
    $response[] = trigger('PATCH', '/address/1', $header, $payload = json_encode($params));

    $response[] = trigger('DELETE', '/registration/1', $header, $payload = '');
    $response[] = trigger('DELETE', '/address/1', $header, $payload = '');

    $response[] = trigger('POST', '/category/config', $header, $payload = '');
}

// Admin User
$res = trigger('POST', '/login', [], $payload = '{"username":"client_1_admin_1", "password":"shames11"}');
if ($res) {
    $response[] = $res;
    $token = $res['body']['Results']['Token'];
    $header = ["Authorization: Bearer {$token}"];

    $response[] = trigger('GET', '/routes', $header, $payload = '');

    $response[] = trigger('DELETE', '/category/truncate', $header, $payload = '');

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
    $response[] = trigger('POST', '/category', $header, $payload = json_encode($params));

    $params = [
        'firstname' => 'Ramesh',
        'lastname' => 'Jangid',
        'email' => 'ramesh@test.com',
        'username' => 'test',
        'password' => 'shames11'
    ];
    $response[] = trigger('POST', '/registration', $header, $payload = json_encode($params));

    $params = [
        'user_id' => 1,
        'address' => '203'
    ];
    $response[] = trigger('POST', '/address', $header, $payload = json_encode($params));

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
    $response[] = trigger('POST', '/registration-with-address', $header, $payload = json_encode($params));

    $response[] = trigger('GET', '/category', $header, $payload = '');
    // $response[] = trigger('GET', '/category/search', $header, $payload = '');
    $response[] = trigger('GET', '/category/1', $header, $payload = '');
    $response[] = trigger('GET', '/category&orderBy={"id":"DESC"}', $header, $payload = '');

    $response[] = trigger('GET', '/registration', $header, $payload = '');
    $response[] = trigger('GET', '/registration/1', $header, $payload = '');

    $response[] = trigger('GET', '/address', $header, $payload = '');
    $response[] = trigger('GET', '/address/1', $header, $payload = '');

    $response[] = trigger('GET', '/registration-with-address', $header, $payload = '');
    $response[] = trigger('GET', '/registration-with-address/1', $header, $payload = '');

    $params = [
        'firstname' => 'Ramesh',
        'lastname' => 'Jangid',
        'email' => 'ramesh@test.com',
        'username' => 'test',
        'password' => 'shames11'
    ];
    $response[] = trigger('PUT', '/registration/1', $header, $payload = json_encode($params));

    $params = [
        'user_id' => 1,
        'address' => '203'
    ];
    $response[] = trigger('PUT', '/address/1', $header, $payload = json_encode($params));

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
    $response[] = trigger('PUT', '/registration-with-address/1', $header, json_encode($params));

    $params = [
        'firstname' => 'Ramesh',
        'lastname' => 'Jangid',
        'email' => 'ramesh_test@test.com'
    ];
    $response[] = trigger('PATCH', '/registration/1', $header, $payload = json_encode($params));

    $params = [
        'address' => '203'
    ];
    $response[] = trigger('PATCH', '/address/1', $header, $payload = json_encode($params));

    $params = [
        'firstname' => 'Ramesh',
        'lastname' => 'Jangid',
        'email' => 'ramesh@test.com',
        'address' => [
            'id' => 1,
            'address' => 'a-203'
        ]
    ];
    $response[] = trigger('PATCH', '/registration-with-address/1', $header, $payload = json_encode($params));

    $response[] = trigger('DELETE', '/registration/1', $header, $payload = '');
    $response[] = trigger('DELETE', '/address/1', $header, $payload = '');

    $params = [
        'address' => [
            'user_id' => 1
        ]
    ];
    $response[] = trigger('DELETE', '/registration-with-address/1', $header, $payload = json_encode($params));

    $response[] = trigger('POST', '/category/config', $header, $payload = '');
}

// print_r($response);
