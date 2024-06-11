<?php
namespace App;

use App\Constants;
use App\Env;
use App\HttpRequest;
use App\HttpResponse;
use App\Logs;

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
     * Inputs
     *
     * @var array
     */
    private $input = null;

    /**
     * Initialize
     *
     * @param array  $input Inputs
     * @return boolean
     */
    public function init(&$input)
    {
        $this->input = $input;
        return HttpResponse::isSuccess();
    }

    /**
     * Process
     *
     * @return boolean
     */
    public function process()
    {
        switch (Constants::$REQUEST_METHOD) {
            case Constants::$POST:
            case Constants::$PUT:
                $this->processUpload();
                break;
        }
        $this->endProcess();

        return HttpResponse::isSuccess();
    }

    /**
     * Function to end process which outputs the results.
     *
     * @return void
     */
    private function endProcess()
    {
        HttpResponse::return2xx(200, 'Success');
        return;
    }
    
    /**
     * Process Upload
     *
     * @return void
     */
    private function processUpload()
    {
        $fileLocation = $this->getLocation();

        $src = fopen("php://input", "rb");
        $dest = fopen($fileLocation, 'w+b');

        stream_copy_to_stream($src, $dest);

        fclose($dest);
        fclose($src);
    }

    /**
     * Function to get filename with location depending uplon $input
     *
     * @return string
     */
    private function getLocation()
    {
        return Constants::$DOC_ROOT . '/Dropbox/' . 'test.txt';
    }
}
