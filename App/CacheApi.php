<?php
namespace App;

use App\Authorize;
use App\JsonEncode;

/**
 * Class initializing Cache Updater
 *
 * This class processes the cache
 *
 * @category   Cache Updater
 * @package    Microservices
 * @author     Ramesh Narayan Jangid
 * @copyright  Ramesh Narayan Jangid
 * @version    Release: @1.0.0@
 * @since      Class available since Release 1.0.0
 */
class CacheApi
{
    /**
     * Authorize class object
     *
     * @var object
     */
    public $authorize = null;

    /**
     * Inputs
     *
     * @var array
     */
    public $input = null;

    /**
     * Initialize
     *
     * @param array  $input     Inputs
     * @param object $authorize Authorize object
     * @return void
     */
    public static function init(&$input, &$authorize)
    {
        self::$input = $input;
        self::$authorize = $authorize
        (new self)->process();
    }

    /**
     * Process all functions
     *
     * @return void
     */
    public function process()
    {
        switch ($_SERVER['REQUEST_METHOD']) {
            case 'PUT':
                $this->processCacheUpdate();
                break;
        }
    }

    /**
     * Process update request
     *
     * @return void
     */
    private function processCacheUpdate()
    {
        // Load Payload
        parse_str(file_get_contents('php://input'), $payloadArr);
        $payload = json_decode($payloadArr['data'], true);
        if (!isset($payload['client_id'])) {
            HttpErrorResponse::return4xx(404, 'Invalid client id');
        }
        if (!in_array($payload['client_id'], $input['readOnlySession']['client_ids'])) {
            HttpErrorResponse::return4xx(404, 'Invalid client id');
        }
        if (!$this->cache->cacheExists($this->authorize->token)) {
            HttpErrorResponse::return4xx(404, 'Invalid token');
        }
        $userInfoArr = json_decode($this->cache->getCache($this->authorize->token), true);
        if (!isset($userInfoArr['id'])) {
            HttpErrorResponse::return4xx(404, 'Invalid token');
        }
        $userInfoArr['client_id'] = $payload['client_id'];
        $tokenDetails = json_decode($this->cache->getCache("user:{$userInfoArr['id']}:token"), true);
        $this->cache->setCache($this->authorize->token, json_encode($userInfoArr), (EXPIRY_TIME - (time() - $tokenDetails['timestamp'])));
        $this->jsonEncodeObj = new JsonEncode();
        $this->jsonEncodeObj->encode(['Status' => 200, 'Message' => 'Success']);
        $this->jsonEncodeObj = null;
    }
}
