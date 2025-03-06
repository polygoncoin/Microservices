<?php
namespace Microservices\App;

/**
 * Database Cache Key
 *
 * Generates Database Cache Key
 *
 * @category   Database Cache Key
 * @package    Microservices
 * @author     Ramesh Narayan Jangid
 * @copyright  Ramesh Narayan Jangid
 * @version    Release: @1.0.0@
 * @since      Class available since Release 1.0.0
 */
class DbCacheKey
{
    /**
     * Get Database Cache Key
     *
     * @param null|int $clientId
     * @param null|int $groupId
     * @return string
     */
    static public function Sql($clientId = null, $groupId = null)
    {
        $app = 'app';
        $client = !is_null($clientId) ? ":c:{$clientId}": '';
        $group = !is_null($groupId) ? ":g:{$groupId}": '';

        switch (true) {
            case (!is_null($clientId) && !is_null($groupId)):
                $return = "{$app}:{$client}:{$group}";
                break;
            case !is_null($clientId):
                $return = "{$app}:{$client}";
                break;
            default:
                $return = $app;
                break;
        }

        return $return;
    }
}
