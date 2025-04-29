<?php

function getCurlConfig($method, $route, $header = [], $json = '')
{
    $homeURL = 'http://api.client001.localhost/Microservices/public_html/index.php';

    $curlConfig[CURLOPT_URL] = "{$homeURL}?r={$route}";
    $curlConfig[CURLOPT_HTTPHEADER] = $header;
    $curlConfig[CURLOPT_HTTPHEADER][] = "X-API-Version: v1.0.0";
    $curlConfig[CURLOPT_HTTPHEADER][] = "Cache-Control: no-cache";

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
        echo "cURL Error #:" . $err;
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
echo '<pre>';

$response[] = trigger('GET', '/reload', [], $jsonPayload = '');

$res = trigger('POST', '/login', [], $jsonPayload = '{"username":"client_1_group_1_user_1", "password":"shames11"}');
if ($res) {
    $response[] = $res;
    $token = $res['Results']['Token'];
    $header = ["Authorization: Bearer {$token}"];

    $response[] = trigger('GET', '/routes', $header, $jsonPayload = '');
    $response[] = trigger('POST', '/category', $header, $jsonPayload = '[{"name":"ramesh0","sub":{"subname":"ramesh1","subsub":[{"subsubname":"ramesh"},{"subsubname":"ramesh"}]}},{"name":"ramesh1","sub":{"subname":"ramesh1","subsub":{"subsubname":"ramesh"}}}]');
    $response[] = trigger('GET', '/category/1', $header, $jsonPayload = '');
    $response[] = trigger('GET', '/category', $header, $jsonPayload = '');
    $response[] = trigger('GET', '/category&orderBy={"id":"DESC"}', $header, $jsonPayload = '');
    $response[] = trigger('POST', '/category/config', $header, $jsonPayload = '');
}

print_r($response);
