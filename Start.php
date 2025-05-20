<?php
namespace Microservices;

use Microservices\Microservices;
use Microservices\App\Logs;

/**
 * Class to autoload class files
 *
 * @category   Autoload
 * @package    Microservices
 * @author     Ramesh Narayan Jangid
 * @copyright  Ramesh Narayan Jangid
 * @version    Release: @1.0.0@
 * @since      Class available since Release 1.0.0
 */
class Autoload
{
    /**
     * Autoload Register function
     *
     * @param string $className
     * @return void
     */
    static public function register($className)
    {
        $className = substr($className, strlen(__NAMESPACE__));
        $className = str_replace("\\", DIRECTORY_SEPARATOR, $className);
        $file = __DIR__ . $className . '.php';
        if (!file_exists($file)) {
            echo PHP_EOL . "File '{$file}' missing" . PHP_EOL;
        }
        require $file;
    }
}

spl_autoload_register(__NAMESPACE__ . '\Autoload::register');

// Process the request
$httpRequestDetails = [];

$httpRequestDetails['server']['host'] = $_SERVER['HTTP_HOST'];
$httpRequestDetails['server']['request_method'] = $_SERVER['REQUEST_METHOD'];
$httpRequestDetails['server']['remote_addr'] = $_SERVER['REMOTE_ADDR'];
if (isset($_SERVER['HTTP_AUTHORIZATION'])) {
    $httpRequestDetails['header']['authorization'] = $_SERVER['HTTP_AUTHORIZATION'];
}
$httpRequestDetails['server']['api_version'] = $_SERVER["HTTP_X_API_VERSION"];
$httpRequestDetails['get'] = &$_GET;

// Code to Initialize / Start the service
try {
    // Check version
    if (
        (!isset($httpRequestDetails['server']) && !isset($httpRequestDetails['server']['api_version']))
        || $httpRequestDetails['server']['api_version'] !== 'v1.0.0'
    ) {
        // Set response headers
        header("Content-Type: application/json; charset=utf-8");
        header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
        header("Pragma: no-cache");

        die('{"Status": 400, "Message": "Bad Request"}');
    }

    $Microservices = new Microservices($httpRequestDetails);

    // Setting CORS
    foreach ($Microservices->getHeaders() as $k => $v) {
        header("{$k}: {$v}");
    }
    if ($httpRequestDetails['server']['request_method'] == 'OPTIONS') {
        exit();
    }

    // Load .env
    $env = parse_ini_file(__DIR__ . DIRECTORY_SEPARATOR . '.env');
    foreach ($env as $key => $value) {
        putenv("{$key}={$value}");
    }

    if ($Microservices->init()) {
        $Microservices->process();
        $Microservices->outputResults();
    }
} catch (\Exception $e) {
    if ($e->getCode() !== 400) {
        list($usec, $sec) = explode(' ', microtime());
        $dateTime = date('Y-m-d H:i:s', $sec) . substr($usec, 1);

        // Log request details
        $logDetails = [
            'LogType' => 'ERROR',
            'DateTime' => $dateTime,
            'HttpDetails' => [
                "HttpCode" => $e->getCode(),
                "HttpMessage" => $e->getMessage()
            ],
            'Details' => [
                '$_GET' => $_GET,
                'php:input' => @file_get_contents('php://input'),
                'session' => $Microservices->c->httpRequest->session
            ]
        ];
        (new Logs)->log($logDetails);
    }

    // Set response code
    http_response_code($e->getCode());

    if ($e->getCode() == 429) {
        header("Retry-After: {$e->getMessage()}");
        $arr = [
            'Status' => $e->getCode(),
            'Message' => 'Too Many Requests',
            'RetryAfter' => $e->getMessage()
        ];
    } else {
        $arr = [
            'Status' => $e->getCode(),
            'Message' => $e->getMessage()
        ];
    }

    // Set response json
    echo json_encode($arr);
}
