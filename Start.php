<?php
namespace Microservices;

use Microservices\Microservices;

class Autoload {
    static public function register($className) {
        $className = str_replace("\\", DIRECTORY_SEPARATOR, $className);
        $file = __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . $className . '.php';
        if (!file_exists($file)) {
            die(json_encode(['Status' => 501, 'Message' => "Class File '{$className}' missing"]));
        }
        require $file;
    }
}

spl_autoload_register(__NAMESPACE__ . '\Autoload::register');

$Microservices = new Microservices();
if ($Microservices->init()) {
    $Microservices->process();
}
$Microservices->outputResults();
