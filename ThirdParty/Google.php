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
        // Create and call functions to manage third party cURL calls here.
        // In functions one can also perform DB operations with $this->authorize object
        // $this->authorize->connectClientDB();

        // ...

        // End the calls with json response with jsonEncode Object.
        $this->endProcess();
    }

    /**
     * Function to end process which outputs the results.
     *
     * @return void
     */
    private function endProcess()
    {
        HttpErrorResponse::return2xx(200, 'Success');
    }
}
