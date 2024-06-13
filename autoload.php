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
 * Returns Start IP and End IP for a given CIDR
 *
 * @param  string $cidrs IP address range in CIDR notation for check
 * @return array
 */
function cidrsIpNumber($cidrs)
{
    $response = [];
    foreach (explode(',', str_replace(' ', '', $cidrs)) as $cidr) {
        if (strpos($cidr, '/')) {
            list($cidrIp, $bits) = explode('/', str_replace(' ', '', $cidr));
            $binCidrIpStr = str_pad(decbin(ip2long($cidrIp)), 32, 0, STR_PAD_LEFT);
            $startIpNumber = bindec(str_pad(substr($binCidrIpStr, 0, $bits), 32, 0, STR_PAD_RIGHT));
            $endIpNumber = $startIpNumber + pow(2, $bits) - 1;
            $response[] = [
                'start' => $startIpNumber,
                'end' => $endIpNumber
            ];
        } else {
            if ($ipNumber = ip2long($cidr)) {
                $response[] = [
                    'start' => $ipNumber,
                    'end' => $ipNumber
                ];    
            }
        }
    }
    return $response;
}
