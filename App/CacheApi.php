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
    public $authorizeObj = null;

    /**
     * Initialize
     *
     * @return void
     */
    public static function init()
    {
        (new self)->process();
    }

    /**
     * Process all functions
     *
     * @return void
     */
    public function process()
    {
        $this->authorizeObj = new Authorize();
        $this->authorizeObj->init();

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
        // input details
        $input = [];

        // Load uriParams
        $input['uriParams'] = $this->authorizeObj->routeParams;

        // Load Read Only Session
        $input['readOnlySession'] = $this->authorizeObj->readOnlySession;

        // Load Payload
        parse_str(file_get_contents('php://input'), $payloadArr);
        $payload = json_decode($payloadArr['data'], true);
        if (!isset($payload['client_id'])) {
            HttpErrorResponse::return4xx(404, 'Invalid client id');
        }
        if (!in_array($payload['client_id'], $input['readOnlySession']['client_ids'])) {
            HttpErrorResponse::return4xx(404, 'Invalid client id');
        }
        if (!$this->cache->cacheExists($this->authorizeObj->token)) {
            HttpErrorResponse::return4xx(404, 'Invalid token');
        }
        $userInfoArr = json_decode($this->cache->getCache($this->authorizeObj->token), true);
        if (!isset($userInfoArr['id'])) {
            HttpErrorResponse::return4xx(404, 'Invalid token');
        }
        $userInfoArr['client_id'] = $payload['client_id'];
        $tokenDetails = json_decode($this->cache->getCache("user:{$userInfoArr['id']}:token"), true);
        $this->cache->setCache($this->authorizeObj->token, json_encode($userInfoArr), (EXPIRY_TIME - (time() - $tokenDetails['timestamp'])));
        $this->jsonEncodeObj = new JsonEncode();
        $this->jsonEncodeObj->encode(['Status' => 200, 'Message' => 'Success']);
        $this->jsonEncodeObj = null;
    }
}
