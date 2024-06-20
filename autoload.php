<?php
class Autoload {
    static public function register($className) {
        $className = str_replace("\\", DIRECTORY_SEPARATOR, $className);
        $file = __DIR__ . DIRECTORY_SEPARATOR . $className . '.php';
        if (!file_exists($file)) {
            die(json_encode(['Status' => 501, 'Message' => "Class File '{$className}' missing"]));
        }
        require $file;
    }
}

spl_autoload_register('Autoload::register');
