<?php

function getCurlConfig($method, $route, $header = [], $json = '')
{
    $homeURL = 'http://api.client001.localhost/Microservices/public_html/index.php';

    $curlConfig[CURLOPT_URL] = "{$homeURL}?r={$route}";
    $curlConfig[CURLOPT_HTTPHEADER] = $header;
    $curlConfig[CURLOPT_HTTPHEADER][] = "Cache-Control: no-cache";
    $curlConfig[CURLOPT_HTTPHEADER][] = 'Content-Type: text/plain; charset=utf-8';
    
    switch ($method) {
        case 'GET':
            break;
        case 'POST':
            $curlConfig[CURLOPT_POST] = true;
            $curlConfig[CURLOPT_POSTFIELDS] = $json;
            break;
        case 'PUT':
            $curlConfig[CURLOPT_CUSTOMREQUEST] = 'PUT';
            $curlConfig[CURLOPT_POSTFIELDS] = $json;
            break;
        case 'PATCH':
            $curlConfig[CURLOPT_CUSTOMREQUEST] = 'PATCH';
            $curlConfig[CURLOPT_POSTFIELDS] = $json;
            break;
        case 'DELETE':
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
        echo "cURL Error #:" . $err;
    } else {
        $response = json_decode($responseJSON, true);
        if ($response['Status'] == 200) {
            echo 'Sucess:'.$route . PHP_EOL;
        } else {
            echo 'Failed:'.$route . PHP_EOL;
        }
    }
    return $response;
}

$response = [];
echo '<pre>';

$response[] = trigger('GET', '/reload', [], '');

$response[] = trigger('POST', '/login', [], '{"username":"client_1_group_1_user_1", "password":"shames11"}');
$token = $response['Results']['Token'];
$header = ["Authorization: Bearer {$token}"];

$response[] = trigger('GET', '/routes', $header, '');
$response[] = trigger('POST', '/category', $header, '[{"name":"ramesh0","sub":{"subname":"ramesh1","subsub":{"subsubname":"ramesh"}}},{"name":"ramesh1","sub":{"subname":"ramesh1","subsub":{"subsubname":"ramesh"}}}]');
$response[] = trigger('GET', '/category', $header, '');
$response[] = trigger('POST', '/category/config', $header, '');

print_r($response);
