<?php
namespace Custom;

use App\Constants;
use App\Env;
use App\HttpRequest;
use App\HttpResponse;
use App\Logs;
use App\Servers\Database\Database;

/**
 * Class to initialize api HTTP request
 *
 * This class process the api request
 *
 * @category   Custom API
 * @package    Microservices
 * @author     Ramesh Narayan Jangid
 * @copyright  Ramesh Narayan Jangid
 * @version    Release: @1.0.0@
 * @since      Class available since Release 1.0.0
 */
class CustomApi
{
    /**
     * DB Server connection object
     *
     * @var object
     */
    public $db = null;

    /**
     * JsonEncode class object
     *
     * @var object
     */
    public $jsonObj = null;

    /**
     * Initialize
     *
     * @return void
     */
    public function init()
    {
        Env::$globalDB = Env::$defaultDbDatabase;
        Env::$clientDB = Env::$dbDatabase;

        $Constants = 'App\\Constants';
        $Env = 'App\\Env';
        $HttpRequest = 'App\\HttpRequest';

        $this->db = Database::getObject();
        $this->jsonObj = HttpResponse::getJsonObject();

        $this->processCustomApi();
    }

    /**
     * Process all functions
     *
     * @return void
     */
    private function processCustomApi()
    {
        return ;
    }
}
