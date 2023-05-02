<?php
namespace App;

/**
 * Trait for PHP functions
 *
 * This trait constains only one function so that one can execute inbuilt PHP
 * functions in strings enclosed with double quotes
 *
 * @category   PHP Functions
 * @package    Microservices
 * @author     Ramesh Narayan Jangid
 * @copyright  Ramesh Narayan Jangid
 * @version    Release: @1.0.0@
 * @since      Class available since Release 1.0.0
 */
trait PHPTrait
{
    /**
     * Function to help execute PHP functions enclosed with double quotes.
     *
     * @param $param Returned values by PHP inbuilt functions.
     */
    function execPhpFunc($param) { return $param; }
}