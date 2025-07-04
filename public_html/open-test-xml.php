<?php

function getCurlConfig($method, $route, $header = [], $payload = '')
{
    $homeURL = 'http://public.localhost/Microservices/public_html/index.php';

    $curlConfig[CURLOPT_URL] = "{$homeURL}?r={$route}&inputDataRepresentation=Xml&outputDataRepresentation=Xml";
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
        $response = 'cURL Error #:' . $error;
        echo 'Failed:'.$route . PHP_EOL;
        echo 'O/P:' . htmlspecialchars($responseBody) . PHP_EOL . PHP_EOL;
        die;
    } else {
        $response = $responseBody;
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

function genXmlPayload(&$params, &$payload, $rowsFlag = false)
{
    if (empty($params)) {
        return;
    }

    $rows = false;

    $isAssoc = (isset($params[0])) ? false : true;

    if (!$isAssoc && count($params) === 1) {
        $params = $params[0];
        if (empty($params)) {
            return;
        }
        $isAssoc = true;
    }

    if (!$isAssoc) {
        $payload .= "<Rows>";
        $rows = true;
    }

    if ($rowsFlag) {
        $payload .= "<Row>";
    }
    foreach ($params as $key => &$value) {
        if ($isAssoc) {$payload .= "<{$key}>";}
        if (is_array($value)) {
            genXmlPayload($value, $payload, $rows);
        } else {
            $payload .= htmlspecialchars($value);
        }
        if ($isAssoc) {$payload .= "</{$key}>";}
    }
    if ($rowsFlag) {
        $payload .= "</Row>";
    }
    if (!$isAssoc) {
        $payload .= "</Rows>";
    }
}

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
genXmlPayload($params, $payload);

$response = trigger('POST', '/registration-with-address', $header, $payload);

header("Content-type: text/xml");
echo $response['body'];
