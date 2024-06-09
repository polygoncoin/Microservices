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
 * Validates subnet specified by CIDR notation.of the form IP address followed by 
 * a '/' character and a decimal number specifying the length, in bits, of the subnet
 * mask or routing prefix (number from 0 to 32).
 *
 * @param  $ip     IP address to check
 * @param  $cidr   IP address range in CIDR notation for check
 * @return boolean true match found otherwise false
 */
function cidr_match($ip, $cidr) {
    $outcome = false;
    $pattern = '/^(([01]?\d?\d|2[0-4]\d|25[0-5])\.){3}([01]?\d?\d|2[0-4]\d|25[0-5])\/(\d{1}|[0-2]{1}\d{1}|3[0-2])$/';
    if (preg_match($pattern, $cidr)){
        list($subnet, $mask) = explode('/', $cidr);
        if (ip2long($ip) >> (32 - $mask) == ip2long($subnet) >> (32 - $mask)) {
            $outcome = true;
        }
    }
    return $outcome;
}

$env = parse_ini_file(__DIR__ . '/.env');
foreach ($env as $key => $value) {
    putenv("{$key}={$value}");
}
