<?php
namespace Crons;

use App\JsonEncode;

/**
 * Class for a particular cron.
 *
 * This class is meant for cron
 * One can initiate cron via access URL to this class
 * https://domain.tld/crons/className?queryString
 * All HTTP methods are supported
 *
 * @category   Crons
 * @package    Microservices
 * @author     Ramesh Narayan Jangid
 * @copyright  Ramesh Narayan Jangid
 * @version    Release: @1.0.0@
 * @since      Class available since Release 1.0.0
 */
class Cron
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
     * Initialize cron
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
        // Create and call functions to manage cron functionality here.
        // In functions one can also perform DB operations with $this->authorize object
        // $this->authorize->connectClientDB();

        // ...

        // End the calls with json response with jsonEncode Object.
        $response = ['Status' => 200, 'Message' => 'message as desited.'];
        $this->jsonEncodeObj = new JsonEncode();
        $this->jsonEncodeObj->encode($response);
        $this->jsonEncodeObj = null;
    }
}
