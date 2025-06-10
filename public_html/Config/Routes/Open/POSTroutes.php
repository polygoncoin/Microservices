<?php
namespace Microservices\public_html\Config\Routes\Auth\GroupRoutes\AdminGroup;

return [
    'registration' => [
        '__FILE__' => $Constants::$PUBLIC_HTML . DIRECTORY_SEPARATOR . 'Config' . DIRECTORY_SEPARATOR . 'Queries' . DIRECTORY_SEPARATOR . 'Open' . DIRECTORY_SEPARATOR . 'POST' . DIRECTORY_SEPARATOR . 'Registration.php',
    ],
    'registration-with-address' => [
        '__FILE__' => $Constants::$PUBLIC_HTML . DIRECTORY_SEPARATOR . 'Config' . DIRECTORY_SEPARATOR . 'Queries' . DIRECTORY_SEPARATOR . 'Open' . DIRECTORY_SEPARATOR . 'POST' . DIRECTORY_SEPARATOR . 'Registration-With-Address.php',
    ],
];
