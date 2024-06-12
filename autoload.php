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
function cidr_match($ip, $cidr)
{
    $response = false;
    if (strpos($cidr, '/')) {
        list($cidrIp, $bits) = explode('/', $cidr);
        $ip = ip2long($ip);
        $binCidrIpStr = str_pad(decbin(ip2long($cidrIp)), 32, 0, STR_PAD_LEFT);
        $start = bindec(str_pad(substr($binCidrIpStr, 0, (32 - $bits)), 32, 0, STR_PAD_RIGHT));
        $end = $start + pow(2, $bits) - 1;
        return ($start <= $ip && $ip <= $end);
    } else {
        $response = ($ip === $cidr);
    }
    return $response;
}
