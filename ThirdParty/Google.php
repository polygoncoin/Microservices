<?php
namespace ThirdParty;

use App\Constants;
use App\Env;
use App\HttpRequest;
use App\HttpResponse;

/**
 * Class for third party - Google.
 *
 * This class perform third party - Google operations.
 * One can initiate third party calls via access to URL
 * https://domain.tld/client/thirdParty/className?queryString
 * All HTTP methods are supported
 *
 * @category   Third party sample
 * @package    Microservices
 * @author     Ramesh Narayan Jangid
 * @copyright  Ramesh Narayan Jangid
 * @version    Release: @1.0.0@
 * @since      Class available since Release 1.0.0
 */
class Google
{
    /**
     * JsonEncode class object
     *
     * @var object
     */
    private $jsonObj = null;

    /**
     * Initialize
     *
     * @return boolean
     */
    public function init()
    {
        $this->jsonObj = HttpResponse::getJsonObject();
        return HttpResponse::isSuccess();
    }

    /**
     * Process
     *
     * @return boolean
     */
    public function process()
    {
        // Create and call functions to manage third party cURL calls here.

        $curl_handle=curl_init();
        curl_setopt($curl_handle,CURLOPT_URL,'https://api.ipify.org?format=json');
        curl_setopt($curl_handle,CURLOPT_CONNECTTIMEOUT,2);
        curl_setopt($curl_handle,CURLOPT_RETURNTRANSFER,1);
        $output = curl_exec($curl_handle);
        curl_close($curl_handle);
        if (empty($output)){
            $output = ['Error' => 'Nothing returned by ipify'];
            App\HttpResponse::$httpStatus = 501;
        } else {
            $output = json_decode($output, true);
        }
        // End the calls with json response with jsonEncode Object.
        $this->endProcess($output);
        return HttpResponse::isSuccess();
    }

    /**
     * Function to end process which outputs the results.
     *
     * @param string $output
     * @return void
     */
    private function endProcess($output)
    {
        $this->jsonObj->addKeyValue('Results', $output);
    }
}
