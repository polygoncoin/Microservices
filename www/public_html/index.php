<?php

use Microservices\App\Constant;
use Microservices\App\Env;
use Microservices\App\HttpStatus;
use Microservices\App\SessionHandler\Session;
use Microservices\App\Reload;
use Microservices\App\Start;
use Microservices\TestCase\Test;

define('ROOT', realpath(path: __DIR__ . '/../../'));
define('ROUTE_URL_PARAM', 'route');

require_once ROOT . DIRECTORY_SEPARATOR . 'Autoload.php';
spl_autoload_register(
	callback:  'Microservices\Autoload::register'
);

ini_set(option: 'display_errors', value: Constant::$TRUE);
error_reporting(error_level: E_ALL);

// Load .env(s)
foreach ([
	'.env'
] as $envFilename) {
	$envDataArray = parse_ini_file(
		filename: ROOT . DIRECTORY_SEPARATOR . $envFilename
	);
	foreach ($envDataArray as $envVarName => $envVarValue) {
		putenv(
			assignment: "{$envVarName}={$envVarValue}"
		);
	}
}

Constant::init();
Env::$timestamp = time();
Env::init();

// Process the request
$httpReqData = [];

$httpReqData['streamData'] = Constant::$TRUE;
$httpReqData['server']['domainName'] = $_SERVER['HTTP_HOST'];
$httpReqData['server']['httpRequestMethod'] = $_SERVER['REQUEST_METHOD'];

if (
	((int)getenv('DISABLE_REQUESTS_VIA_PROXIES')) === 1
	&& !isset($_SERVER['REMOTE_ADDR'])
) {
	die('Invalid request');
}

$httpReqData['server']['httpRequestIp'] = getHttpRequestIp();

$httpReqData['header'] = getallheaders();

if (isset($httpReqData['header']['Content-Type'])) {
	$httpReqData['header']['contentType'] = $httpReqData['header']['Content-Type'];
} else {
	$httpReqData['header']['contentType'] = '';
}
if (isset($_SERVER['Range'])) {
	$httpReqData['header']['range'] = $_SERVER['Range'];
}
if (isset($_SERVER['HTTP_USER_AGENT'])) {
	$httpReqData['header']['userAgent'] = $_SERVER['HTTP_USER_AGENT'];
}
if (isset($_SERVER['HTTP_AUTHORIZATION'])) {
	$httpReqData['header']['tokenHeader'] = $_SERVER['HTTP_AUTHORIZATION'];
}

$httpReqData['get'] = &$_GET;
if (isset($httpReqData['get'][ROUTE_URL_PARAM])) {
	$httpReqData['get'][ROUTE_URL_PARAM] = '/' . trim(
		string: $httpReqData['get'][ROUTE_URL_PARAM],
		characters: '/'
	);
} else {
	die('Missing route');
}

$httpReqData['post'] = file_get_contents(
	filename: 'php://input'
);
$httpReqData['files'] = parseMultipartInput($httpReqData);

$httpReqData['isWebRequest'] = Constant::$TRUE;
$httpReqData['httpRequestHash'] = httpRequestHash(
	hashArray: [
		$_SERVER['HTTP_ACCEPT_ENCODING'] ?? '',
		$_SERVER['HTTP_ACCEPT_LANGUAGE'] ?? '',
		$_SERVER['HTTP_ACCEPT'] ?? '',
		$_SERVER['HTTP_USER_AGENT'] ?? ''
	]
);

if (
	isset($httpReqData['get'][ROUTE_URL_PARAM])
	&& in_array(
		needle: $httpReqData['get'][ROUTE_URL_PARAM],
		haystack: [
			'/all-test',
			'/auth-test',
			'/open-test',
			'/open-test-xml',
			'/supp-test'
		],
		strict: Constant::$TRUE
	)
	&& $httpReqData['server']['domainName'] === 'localhost'
) {
	$testObject = new Test($httpReqData);
	switch ($httpReqData['get'][ROUTE_URL_PARAM]) {
		case '/all-test':
			echo '<pre>'.print_r(value: $testObject->processAllTest(), return: Constant::$TRUE);
			break;
		case '/auth-test':
			echo '<pre>'.print_r(value: $testObject->processPrivate(), return: Constant::$TRUE);
			break;
		case '/open-test':
			echo '<pre>'.print_r(value: $testObject->processPublic(), return: Constant::$TRUE);
			break;
		case '/open-test-xml':
			echo '<pre>'.print_r(value: $testObject->processPublicXml(), return: Constant::$TRUE);
			break;
		case '/supp-test':
			echo '<pre>'.print_r(value: $testObject->processPrivateSupplement(), return: Constant::$TRUE);
			break;
	}
} else {
	if ($httpReqData['get'][ROUTE_URL_PARAM] === '/' . Env::$SYSTEM_RELOAD_REQUEST_KEYWORD) {
		Reload::process(
			httpRequestIp: $httpReqData['server']['httpRequestIp']
		);
		return Constant::$FALSE;
	} else {
		ob_start();
		[
			$responseHeaderArray,
			$responseContent,
			$responseCode
		] = Start::http(
			httpReqData: $httpReqData
		);
		@ob_clean();

		$responseCode = $responseCode ?? HttpStatus::$Ok;
		http_response_code(response_code: $responseCode);

		foreach ($responseHeaderArray as $headerName => $headerValue) {
			header(
				header: "{$headerName}: {$headerValue}"
			);
		}

		die($responseContent);
	}
}

/**
 * Unique HTTP request hash
 *
 * @param array $hashArray Hash array
 *
 * @return string
 */
function httpRequestHash($hashArray): string
{
	return md5(
		json_encode(
			value: $hashArray
		)
	);
}

/**
 * Get request IP
 *
 * @return string
 */
function getHttpRequestIp() {
	// Check for shared internet connections (e.g., Cloudflare, proxy)
	if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
		$ip = $_SERVER['HTTP_CLIENT_IP'];
	}
	// Check if the user is behind a proxy and the IP is forwarded
	elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
		// HTTP_X_FORWARDED_FOR can contain a comma-separated list of IPs
		// The first one is typically the original customer IP
		$ipList = explode(
			',',
			$_SERVER['HTTP_X_FORWARDED_FOR']
		);
		$ip = trim($ipList[0]);
	}
	// Default method: get the remote address directly
	else {
		$ip = $_SERVER['REMOTE_ADDR'];
	}
	return $ip;
}

/**
 * Parse Multipart Input
 *
 * @param array $httpReqData HTTP request data
 *
 * @return array
 */
function parseMultipartInput($httpReqData) {
	$FILES = [];
	// 1. Verify content type and extract boundary
	if (!preg_match('/boundary=(.*)$/', $httpReqData['header']['contentType'], $matches)) {
		return;
	}
	$boundary = $matches[1];

	// 2. Read the raw stream block by block (memory-safe approach)
	$raw_data = $httpReqData['post'];

	if (empty($raw_data)) {
		return;
	}

	// 3. Split the stream using the boundary marker
	$parts = explode("--" . $boundary, $raw_data);

	foreach ($parts as $part) {
		$part = ltrim($part, "\r\n");
		if (empty($part) || $part === "--\r\n" || $part === "--") {
			continue;
		}

		// Separate headers from the binary file payload
		list($headers_block, $body) = explode("\r\n\r\n", $part, 2);
		// Trim trailing carriage return added by boundary layout
		if (substr($body, -2) === "\r\n") {
			$body = substr($body, 0, -2);
		}

		// Parse individual section headers
		$headers = [];
		foreach (explode("\r\n", $headers_block) as $line) {
			list($key, $val) = explode(": ", $line, 2);
			$headers[strtolower($key)] = $val;
		}

		// 4. Look for Content-Disposition to check if it's a file component
		if (isset($headers['content-disposition'])) {
			preg_match('/name="([^"]*)"/', $headers['content-disposition'], $nameMatch);
			$inputName = $nameMatch[1] ?? '';

			if (preg_match('/filename="([^"]*)"/', $headers['content-disposition'], $fileMatch)) {
				// It's a file component!
				$originalName = $fileMatch[1];
				$mimeType = $headers['content-type'] ?? 'application/octet-stream';

				// Write binary body data into a secure system tmp file
				$tmpPath = tempnam(sys_get_temp_dir(), 'php_upload_');
				file_put_contents($tmpPath, $body);

				// 5. Explicitly populate the $_FILES global array
				$FILES[$inputName] = [
					'name' => $originalName,
					'type' => $mimeType,
					'tmp_name' => $tmpPath,
					'error'	=> UPLOAD_ERR_OK,
					'size' => filesize($tmpPath)
				];
			}
		}
		return $FILES;
	}
}
