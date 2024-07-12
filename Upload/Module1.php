<?php
namespace Microservices\Upload;

use Microservices\App\Constants;
use Microservices\App\Env;
use Microservices\App\HttpRequest;
use Microservices\App\HttpResponse;
use Microservices\App\Logs;
use Microservices\App\Servers\Cache\Cache;
use Microservices\App\Servers\Database\Database;
use Microservices\Upload\UploadTrait;

/**
 * Class is used for file uploads
 *
 * This class supports POST & PUT HTTP request
 *
 * @category   Upload Module 1
 * @package    Microservices
 * @author     Ramesh Narayan Jangid
 * @copyright  Ramesh Narayan Jangid
 * @version    Release: @1.0.0@
 * @since      Class available since Release 1.0.0
 */
class Module1
{
    use UploadTrait;

    /**
     * JsonEncode class object
     *
     * @var object
     */
    private $jsonEncode = null;

    /**
     * Initialize
     *
     * @return boolean
     */
    public function init()
    {
        $this->jsonEncode = HttpResponse::getJsonObject();
        return HttpResponse::isSuccess();
    }

    /**
     * Process
     *
     * @return boolean
     */
    public function process()
    {
        $absFilePath = $this->getLocation();
        $this->saveFile($absFilePath);

        return HttpResponse::isSuccess();
    }

    /**
     * Function to get filename with location depending uplon $input
     *
     * @return string
     */
    private function getLocation()
    {
        return Constants::$DOC_ROOT . '/Dropbox/' . 'test.png';
    }
}
