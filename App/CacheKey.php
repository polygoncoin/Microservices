<?php
namespace Microservices\App;

/**
 * Cache Key
 *
 * Generates Cache Key
 *
 * @category   Cache Key
 * @package    Microservices
 * @author     Ramesh Narayan Jangid
 * @copyright  Ramesh Narayan Jangid
 * @version    Release: @1.0.0@
 * @since      Class available since Release 1.0.0
 */
class CacheKey
{
    static public function Client($hostname)
    {
        return "c:{$hostname}";
    }

    static public function ClientUser($clientId, $username)
    {
        return "cu:{$clientId}:u:{$username}";
    }

    static public function Group($groupId)
    {
        return "g:{$groupId}";
    }

    static public function CIDR($groupId)
    {
        return "cidr:{$groupId}";
    }

    static public function Token($token)
    {
        return "t:{$token}";
    }

    static public function UserToken($userId)
    {
        return "ut:{$userId}";
    }
}
