<?php
namespace Microservices;

use Microservices\App\RateLimiter;
use Microservices\Microservices;

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

// Load .env
$env = parse_ini_file(__DIR__ . '/.rate-limit.env');
foreach ($env as $key => $value) {
    putenv("{$key}={$value}");
}

$RateLimiterHost = getenv('RateLimiterHost');
$RateLimiterHostPort = getenv('RateLimiterHostPort');
$RateLimiterIPMaxRequests = getenv('RateLimiterIPMaxRequests');
$RateLimiterIPSecondsWindow = getenv('RateLimiterIPSecondsWindow');
$RateLimiterIPPrefix = getenv('RateLimiterIPPrefix');

try {
    $rateLimiter = new RateLimiter(
        $RateLimiterHost,
        $RateLimiterHostPort,
        $RateLimiterIPPrefix,
        $RateLimiterMaxRequests,
        $RateLimiterSecondsWindow
    );

    // Check rate limit for request from IP
    $result = $rateLimiter->check($_SERVER['REMOTE_ADDR']);

    if ($result['allowed']) {
        // Process the request
        $httpRequestDetails = [];

        $httpRequestDetails['server']['host'] = $_SERVER['HTTP_HOST'];
        $httpRequestDetails['server']['request_method'] = $_SERVER['REQUEST_METHOD'];
        $httpRequestDetails['server']['remote_addr'] = $_SERVER['REMOTE_ADDR'];
        if (isset($_SERVER['HTTP_AUTHORIZATION'])) {
            $httpRequestDetails['header']['authorization'] = $_SERVER['HTTP_AUTHORIZATION'];
        }
        $httpRequestDetails['get'] = &$_GET;
        
        // Code to Initialize / Start the service.
        try {
            $Microservices = new Microservices($httpRequestDetails);
        
            // Setting CORS
            foreach ($Microservices->getCors() as $k => $v) {
                header("{$k}: {$v}");
            }
            if ($httpRequestDetails['server']['request_method'] == 'OPTIONS') {
                exit();
            }
        
            if ($Microservices->init()) {
                $Microservices->process();
                $Microservices->outputResults();
            }
        } catch (\Exception $e) {
            $arr = [
                'Status' => $e->getCode(),
                'Message' => $e->getMessage()
            ];
            echo json_encode($arr);
        }
    } else {
        // Return 429 Too Many Requests
        http_response_code(429);
        header('Retry-After: ' . ($result['resetAt'] - time()));
        echo json_encode([
            'error' => 'Too Many Requests',
            'retryAfter' => $result['resetAt']
        ]);
    }

} catch (Exception $e) {
    // Handle connection errors
    // error_log('Rate limiter error: ' . $e->getMessage());
    echo('Rate limiter error: ' . $e->getMessage());
}
