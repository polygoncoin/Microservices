<?php
namespace App;

use App\Constants;
use App\HttpRequest;
use App\HttpResponse;

/**
 * Class to initialize api HTTP request
 *
 * This class process the api request
 *
 * @category   Routes
 * @package    Microservices
 * @author     Ramesh Narayan Jangid
 * @copyright  Ramesh Narayan Jangid
 * @version    Release: @1.0.0@
 * @since      Class available since Release 1.0.0
 */
class Routes
{
    /**
     * Initialize
     *
     * @return void
     */
    public function init()
    {
        HttpRequest::init();
        HttpRequest::loadToken();
        HttpRequest::initSession();
        $this->processRoutes();
    }

    /**
     * Make allowed routes list of a logged-in user
     *
     * @return void
     */
    private function processRoutes()
    {
    }
}
