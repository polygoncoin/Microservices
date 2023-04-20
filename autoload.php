<?php
spl_autoload_register(function ($className) {
    // Adapt this depending on your directory structure
    $className = str_replace("\\", DIRECTORY_SEPARATOR, $className);
	echo $file = __DIR__ . DIRECTORY_SEPARATOR . $className . '.php';
    if (!file_exists($file)) {
        die(json_encode(['Status' => 501, 'Message' => "Class File '{$className}' missing"]));
    }
    require $file;
});

function httpAuthentication()
{
    // Check request not from proxy.
    if (
        !isset($_SERVER['REMOTE_ADDR']) ||
        $_SERVER['REMOTE_ADDR'] !== getenv('HttpAuthenticationRestrictedIp')
    ) {
        http_response_code(404);
    }
    if (!isset($_SERVER['PHP_AUTH_USER']) || !isset($_SERVER['PHP_AUTH_PW'])) {
        header('WWW-Authenticate: Basic realm="Test Authentication System"');
        header('HTTP/1.0 401 Unauthorized');
        echo "You must enter a valid login ID and password to access this resource\n";
        return false;
    } else {
        $valid_passwords = array ("shames11" => "shames11");
        $valid_users = array_keys($valid_passwords);

        $user = $_SERVER['PHP_AUTH_USER'];
        $pass = $_SERVER['PHP_AUTH_PW'];

        $validated = ($user === getenv('HttpAuthenticationUser')) && ($pass === getenv('HttpAuthenticationPassword'));

        if (!$validated) {
            header('WWW-Authenticate: Basic realm="My Realm"');
            header('HTTP/1.0 401 Unauthorized');
            die ("Not authorized");
        } else {
            return true;
        }
    }
}

$env = parse_ini_file(__DIR__ . '/.env');
foreach ($env as $key =>$value) {
    putenv("{$key}={$value}");
}
