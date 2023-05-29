<?php
namespace App;

use App\HttpErrorResponse;

/**
 * Class is used for file uploads
 * 
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
     * JsonEncode class object
     *
     * @var object
     */
    public $jsonEncodeObj = null;

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
        $this->authorize = new Authorize();
        $this->authorize->init();

        switch ($_SERVER['REQUEST_METHOD']) {
            case 'POST':
            case 'PUT':
                $this->processUpload();
                break;
        }
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
    
    /**
     * Process Upload
     *
     * @return void
     */
    private function processUpload()
    {
        // input details
        $input = [];

        // Load uriParams
        $input['uriParams'] = &$this->authorize->routeParams;

        // Load Read Only Session
        $input['readOnlySession'] = &$this->authorize->readOnlySession;

        // Load $_GET as payload
        $input['payload'] = &$_GET;

        $fileLocation = $this->getLocation($input);

        $src = fopen("php://input", "rb");
        $dest = fopen($fileLocation, 'w+b');

        stream_copy_to_stream($src, $dest);

        fclose($dest);
        fclose($src);
    }

    /**
     * Function to get filename with location depending uplon $input
     *
     * @param array $input Inputs
     * @return string
     */
    private function getLocation($input)
    {
        return '';
    }
}
