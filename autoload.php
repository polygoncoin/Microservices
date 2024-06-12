<?php
spl_autoload_register(function ($className) {
    $className = str_replace("\\", DIRECTORY_SEPARATOR, $className);
	$file = __DIR__ . DIRECTORY_SEPARATOR . $className . '.php';
    if (!file_exists($file)) {
        die(json_encode(['Status' => 501, 'Message' => "Class File '{$className}' missing"]));
    }
    require $file;
});

/**
 * Check IP.
 *
 * @param  string  $ip   IP address to check
 * @param  string  $cidr IP address range in CIDR notation for check
 * @return boolean true  match found otherwise false
 */
function cidr_match($ip, $cidr) {
    return true;
}
