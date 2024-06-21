<?php
namespace Microservices;

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
        $className = str_replace("\\", DIRECTORY_SEPARATOR, $className);
        $file = __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . $className . '.php';
        if (!file_exists($file)) {
            die(json_encode(['Status' => 501, 'Message' => "Class File '{$className}' missing"]));
        }
        require $file;
    }
}

spl_autoload_register(__NAMESPACE__ . '\Autoload::register');

// Code to Initialize / Start the service.
$Microservices = new Microservices();
if ($Microservices->init()) {
    $Microservices->process();
}
$Microservices->outputResults();
