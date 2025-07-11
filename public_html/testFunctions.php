<?php // phpcs:ignore PEAR.Commenting.FileComment.Missing
/**
 * Generates raw headers into array
 *
 * @param string $rawHeaders Raw headers from cURL response
 *
 * @return array
 * @throws \Exception
 */
function httpParseHeaders($rawHeaders): array
{
    $headers = array();
    $key = '';

    foreach (explode(separator: "\n", string: $rawHeaders) as $i => $h) {
        $h = explode(separator: ':', string: $h, limit: 2);

        if (isset($h[1])) {
            if (!isset($headers[$h[0]])) {
                $headers[$h[0]] = trim(string: $h[1]);
            } elseif (is_array(value: $headers[$h[0]])) {
                $headers[$h[0]] = array_merge(
                    $headers[$h[0]],
                    array(trim(string: $h[1]))
                );
            } else {
                $headers[$h[0]] = array_merge(
                    array($headers[$h[0]]),
                    array(trim(string: $h[1]))
                );
            }

            $key = $h[0];
        } else {
            if (substr(string: $h[0], offset: 0, length: 1) == "\t") {
                $headers[$key] .= "\r\n\t".trim(string: $h[0]);
            } elseif (!$key) {
                $headers[0] = trim(string: $h[0]);
            }
        }
    }

    return $headers;
}

/**
 * Return cURL Config
 *
 * @param string $homeURL     Site URL
 * @param string $method      HTTP method
 * @param string $route       Route
 * @param string $queryString Query String
 * @param array  $header      Header
 * @param string $payload     Payload
 *
 * @return array
 */
function getCurlConfig(
    $homeURL,
    $method,
    $route,
    $queryString,
    $header = [],
    $payload = ''
): array {
    $curlConfig[CURLOPT_URL] = "{$homeURL}?r={$route}&{$queryString}";
    $curlConfig[CURLOPT_HTTPHEADER] = $header;
    $curlConfig[CURLOPT_HTTPHEADER][] = 'X-API-Version: v1.0.0';
    $curlConfig[CURLOPT_HTTPHEADER][] = 'Cache-Control: no-cache';
    $curlConfig[CURLOPT_HEADER] = 1;

    $contentType = 'Content-Type: text/plain; charset=utf-8';

    switch ($method) {
    case 'GET':
        break;
    case 'POST':
        $curlConfig[CURLOPT_HTTPHEADER][] = $contentType;
        $curlConfig[CURLOPT_POST] = true;
        $curlConfig[CURLOPT_POSTFIELDS] = $payload;
        break;
    case 'PUT':
        $curlConfig[CURLOPT_HTTPHEADER][] = $contentType;
        $curlConfig[CURLOPT_CUSTOMREQUEST] = 'PUT';
        $curlConfig[CURLOPT_POSTFIELDS] = $payload;
        break;
    case 'PATCH':
        $curlConfig[CURLOPT_HTTPHEADER][] = $contentType;
        $curlConfig[CURLOPT_CUSTOMREQUEST] = 'PATCH';
        $curlConfig[CURLOPT_POSTFIELDS] = $payload;
        break;
    case 'DELETE':
        $curlConfig[CURLOPT_HTTPHEADER][] = $contentType;
        $curlConfig[CURLOPT_CUSTOMREQUEST] = 'DELETE';
        $curlConfig[CURLOPT_POSTFIELDS] = $payload;
        break;
    }
    $curlConfig[CURLOPT_RETURNTRANSFER] = true;

    return $curlConfig;
}

/**
 * Trigger cURL
 *
 * @param string $method  HTTP method
 * @param string $route   Route
 * @param array  $header  Header
 * @param string $payload Payload
 *
 * @return mixed
 */
function trigger(
    $method,
    $route,
    $header = [],
    $payload = ''
): mixed {
    // Open
    $homeURL='http://public.localhost/Microservices/public_html/index.php';
    // Auth
    // $homeURL='http://api.client001.localhost/Microservices/public_html/index.php';

    $curl = curl_init();
    $curlConfig = getCurlConfig(
        homeURL: $homeURL,
        method: $method,
        route: $route,
        queryString: $queryString = '',
        header: $header,
        payload: $payload
    );
    curl_setopt_array(handle: $curl, options: $curlConfig);
    $curlResponse = curl_exec(handle: $curl);

    $responseHttpCode = curl_getinfo(
        handle: $curl,
        option: CURLINFO_HTTP_CODE
    );
    $responseContentType = curl_getinfo(
        handle: $curl,
        option: CURLINFO_CONTENT_TYPE
    );

    $headerSize = curl_getinfo(handle: $curl, option: CURLINFO_HEADER_SIZE);
    $responseHeaders = httpParseHeaders(
        rawHeaders: substr(
            string: $curlResponse,
            offset: 0,
            length: $headerSize
        )
    );
    $responseBody = substr(string: $curlResponse, offset: $headerSize);

    $error = curl_error(handle: $curl);
    curl_close(handle: $curl);

    $error = curl_error(handle: $curl);
    curl_close(handle: $curl);

    if ($error) {
        $response = 'cURL Error #:' . $error;
    } else {
        $response = $responseBody;
    }

    // return [
    //     'route' => "{$homeURL}?r={$route}&{$queryString}",
    //     'httpMethod' => $method,
    //     'requestHeaders' => $curlConfig[CURLOPT_HTTPHEADER],
    //     'requestPayload' => $payload,
    //     'responseHttpCode' => $responseHttpCode,
    //     'responseHeaders' => $responseHeaders,
    //     'responseContentType' => $responseContentType,
    //     'responseBody' => $response
    // ];

    return $response;
}

/**
 * Generates XML Payload
 *
 * @param array $params   Params
 * @param array $payload  Payload
 * @param bool  $rowsFlag Flag
 *
 * @return array
 * @throws \Exception
 */
function genXmlPayload(&$params, &$payload, $rowsFlag = false): void
{
    if (empty($params)) {
        return;
    }

    $rows = false;

    $isAssoc = (isset($params[0])) ? false : true;

    if (!$isAssoc && count(value: $params) === 1) {
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
        if ($isAssoc) {
            $payload .= "<{$key}>";
        }
        if (is_array(value: $value)) {
            genXmlPayload(params: $value, payload: $payload, rowsFlag: $rows);
        } else {
            $payload .= htmlspecialchars(string: $value);
        }
        if ($isAssoc) {
            $payload .= "</{$key}>";
        }
    }
    if ($rowsFlag) {
        $payload .= "</Row>";
    }
    if (!$isAssoc) {
        $payload .= "</Rows>";
    }
}
