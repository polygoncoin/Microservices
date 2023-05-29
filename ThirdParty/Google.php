<?php
namespace ThirdParty;

use App\HttpErrorResponse;

/**
 * Class for third party - Google.
 *
 * This class perform third party - Google operations.
 * One can initiate third party calls via access to URL
 * https://domain.tld/thirdParty/className?queryString
 * All HTTP methods are supported
 *
 * @category   Third party
 * @package    Microservices
 * @author     Ramesh Narayan Jangid
 * @copyright  Ramesh Narayan Jangid
 * @version    Release: @1.0.0@
 * @since      Class available since Release 1.0.0
 */
class Google
{
    /**
     * Inputs
     *
     * @var array
     */
    private static $input = null;

    /**
     * Initialize
     *
     * @param array  $input     Inputs
     * @return void
     */
    public static function init(&$input)
    {
        self::$input = $input;
        (new self)->process();
    }

    /**
     * Process all functions
     *
     * @return void
     */
    public function process()
    {
        // Create and call functions to manage third party cURL calls here.

        $curl_handle=curl_init();
        curl_setopt($curl_handle,CURLOPT_URL,'http://polygon.co.in');
        curl_setopt($curl_handle,CURLOPT_CONNECTTIMEOUT,2);
        curl_setopt($curl_handle,CURLOPT_RETURNTRANSFER,1);
        $buffer = curl_exec($curl_handle);
        curl_close($curl_handle);
        if (empty($buffer)){
            $buffer = "Nothing returned from url";
        }

        // End the calls with json response with jsonEncode Object.
        $this->endProcess($buffer);
    }

    /**
     * Function to end process which outputs the results.
     *
     * @param string $result
     * @return void
     */
    private function endProcess($result)
    {
        HttpErrorResponse::return2xx(200, $result);
    }
}
