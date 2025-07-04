<?php
namespace Microservices\public_html\Validation;

/**
 * @category   Validator Trait
 * @package    Microservices
 * @author     Ramesh Narayan Jangid
 * @copyright  Ramesh Narayan Jangid
 * @version    Release: @1.0.0@
 * @since      Class available since Release 1.0.0
 */
trait ValidatorTrait
{
    /**
     * Validate string is alphanumeric
     *
     * @param string $v
     * @return boolean
     */
    private function isAlphanumeric(&$v)
    {
        return preg_match('/^[a-z0-9 .\-]+$/i', $v);
    }

    /**
     * Validate string is an email
     *
     * @param string $v email address
     * @return boolean
     */
    private function isEmail(&$v)
    {
        return filter_var($v, FILTER_VALIDATE_EMAIL);
    }
}