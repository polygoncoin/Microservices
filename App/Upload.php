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
     * Inputs
     *
     * @var array
     */
    private static $input = null;

    /**
     * Initialize
     *
     * @param array  $input Inputs
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
        return __DOC_ROOT__ . '/Dropbox/' . 'test.txt';
    }
}
