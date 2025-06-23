<?php

function getCurlConfig($method, $route, $header = [], $json = '')
{
    $homeURL = 'http://public.localhost/Microservices/public_html/index.php';

    $curlConfig[CURLOPT_URL] = "{$homeURL}?r={$route}";
    // $curlConfig[CURLOPT_URL] = "{$homeURL}?r={$route}&outputDataRepresentation=Xml";
    $curlConfig[CURLOPT_HTTPHEADER] = $header;
    $curlConfig[CURLOPT_HTTPHEADER][] = 'X-API-Version: v1.0.0';
    $curlConfig[CURLOPT_HTTPHEADER][] = 'Cache-Control: no-cache';

    switch ($method) {
        case 'GET':
            break;
        case 'POST':
            $curlConfig[CURLOPT_HTTPHEADER][] = 'Content-Type: text/plain; charset=utf-8';
            $curlConfig[CURLOPT_POST] = true;
            $curlConfig[CURLOPT_POSTFIELDS] = $json;
            break;
        case 'PUT':
            $curlConfig[CURLOPT_HTTPHEADER][] = 'Content-Type: text/plain; charset=utf-8';
            $curlConfig[CURLOPT_CUSTOMREQUEST] = 'PUT';
            $curlConfig[CURLOPT_POSTFIELDS] = $json;
            break;
        case 'PATCH':
            $curlConfig[CURLOPT_HTTPHEADER][] = 'Content-Type: text/plain; charset=utf-8';
            $curlConfig[CURLOPT_CUSTOMREQUEST] = 'PATCH';
            $curlConfig[CURLOPT_POSTFIELDS] = $json;
            break;
        case 'DELETE':
            $curlConfig[CURLOPT_HTTPHEADER][] = 'Content-Type: text/plain; charset=utf-8';
            $curlConfig[CURLOPT_CUSTOMREQUEST] = 'DELETE';
            $curlConfig[CURLOPT_POSTFIELDS] = $json;
            break;
    }
    $curlConfig[CURLOPT_RETURNTRANSFER] = true;

    return $curlConfig;
}

function trigger($method, $route, $header = [], $json = '')
{
    $curl = curl_init();
    $curlConfig = getCurlConfig($method, $route, $header, $json);
    curl_setopt_array($curl, $curlConfig);
    $responseJSON = curl_exec($curl);
    $err = curl_error($curl);
    curl_close($curl);

    if ($err) {
        echo 'cURL Error #:' . $err;
    } else {
        $response = json_decode($responseJSON, true);
        if (
            !empty($response)
            && (
                (isset($response['Status']) && $response['Status'] == 200)
                || (isset($response['Results']['Status']) && $response['Results']['Status'] == 200)
            )
        ) {
            echo 'Sucess:'.$route . PHP_EOL . PHP_EOL;
        } else {
            echo 'Failed:'.$route . PHP_EOL;
            echo 'O/P:' . $responseJSON . PHP_EOL . PHP_EOL;
            $response = false;
        }
    }
    return $response;
}

$response = [];
$header = [];
echo '<pre>';

$payload = [
    'firstname' => 'Ramesh',
    'lastname' => 'Jangid',
    'email' => 'ramesh@test.com',
    'username' => 'test',
    'password' => 'shames11'
];
$response[] = trigger('POST', '/registration', $header, $jsonPayload = json_encode($payload));

$payload = [
    'firstname' => 'Ramesh',
    'lastname' => 'Jangid',
    'email' => 'ramesh@test.com',
    'username' => 'test',
    'password' => 'shames11',
    'address' => [
        'address' => 'A-203'
    ]
];
$response[] = trigger('POST', '/registration-with-address', $header, $jsonPayload = json_encode($payload));

$response[] = trigger('GET', '/category/1', $header, $jsonPayload = '');
// $response[] = trigger('GET', '/category/search', $header, $jsonPayload = '');
$response[] = trigger('GET', '/category', $header, $jsonPayload = '');
$response[] = trigger('GET', '/category&orderBy={"id":"DESC"}', $header, $jsonPayload = '');

print_r($response);
