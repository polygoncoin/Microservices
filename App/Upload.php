<?php
namespace Microservices\App;

use Microservices\App\HttpResponse;
use Microservices\Upload\UploadApi;

/**
 * Class is used for file uploads
 *
 * This class supports POST & PUT HTTP request
 *
 * @category   Upload
 * @package    Microservices
 * @author     Ramesh Narayan Jangid
 * @copyright  Ramesh Narayan Jangid
 * @version    Release: @1.0.0@
 * @since      Class available since Release 1.0.0
 */
class Upload
{
    /**
     * Initialize
     *
     * @return boolean
     */
    public function init()
    {
        return HttpResponse::isSuccess();
    }

    /**
     * Process
     *
     * @return boolean
     */
    public function process()
    {
        $api = new UploadApi();
        if ($api->init()) {
            $api->process();
        }

        return HttpResponse::isSuccess();
    }
}
