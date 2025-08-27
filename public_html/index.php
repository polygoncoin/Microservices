<?php
/**
 * Validator
 * php version 8.3
 *
 * @category  Validator
 * @package   Microservices
 * @author    Ramesh N Jangid <polygon.co.in@gmail.com>
 * @copyright 2025 Ramesh N Jangid
 * @license   MIT https://opensource.org/license/mit
 * @link      https://github.com/polygoncoin/Microservices
 * @since     Class available since Release 1.0.0
 */
namespace Microservices\public_html;

ini_set(option: 'display_errors', value: true);
error_reporting(error_level: E_ALL);

define(
    constant_name: 'PUBLIC_HTML',
    value: realpath(path: __DIR__ . DIRECTORY_SEPARATOR . '..')
);

require_once  PUBLIC_HTML . DIRECTORY_SEPARATOR . 'Start.php';
