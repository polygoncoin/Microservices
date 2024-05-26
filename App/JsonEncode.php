<?php
namespace App;

use App\Logs;
use App\HttpRequest;

/**
 * Creates JSON
 *
 * This class is built to avoid creation of large array objects
 * (which leads to memory limit issues for larger data set)
 * which are then converted to JSON. This class gives access to
 * create JSON in parts for what ever smallest part of data
 * we have of the large data set which are yet to be fetched.
 *
 * @category   JSON Encoder
 * @package    Microservices
 * @author     Ramesh Narayan Jangid
 * @copyright  Ramesh Narayan Jangid
 * @version    Release: @1.0.0@
 * @since      Class available since Release 1.0.0
 */
class JsonEncoder
{
    /**
     * Temporary Stream
     *
     * @var string
     */
    private $tempStream = '';

    /**
     * Array of JsonEncoderObject objects
     *
     * @var array
     */
    private $objects = [];

    /**
     * Current JsonEncoderObject object
     *
     * @var object
     */
    private $currentObject = null;

    /**
     * JsonEncode constructor
     */
    public function __construct()
    {
        if (REQUEST_METHOD === 'GET') {
            $this->tempStream = fopen("php://temp", "w+b");
        } else {
            $this->tempStream = fopen("php://memory", "w+b");
        }
    }

    /**
     * Write to temporary stream
     * 
     * @return void
     */
    public function write($str)
    {
        fwrite($this->tempStream, $str);
    }

    /**
     * Escape the json string key or value
     *
     * @param string $str json key or value string.
     * @return string
     */
    private function escape($str)
    {
        if (is_null($str)) return 'null';
        $escapers = array("\\", "/", "\"", "\n", "\r", "\t", "\x08", "\x0c");
        $replacements = array("\\\\", "\\/", "\\\"", "\\n", "\\r", "\\t", "\\f", "\\b");
        $str = str_replace($escapers, $replacements, $str);
        $this->write('"' . $str . '"');
    }

    /**
     * Encodes both simple and associative array to json
     *
     * @param $arr string value escaped and array value json_encode function is applied.  
     * @return void
     */
    public function encode($arr)
    {
        if ($this->currentObject) {
            $this->write($this->currentObject->comma);
        }
        if (is_array($arr)) {
            $this->write(json_encode($arr));
        } else {
            $this->write($this->escape($arr));
        }
        if ($this->currentObject) {
            $this->currentObject->comma = ',';
        }
    }

    /**
     * Add simple array/value as in the json format.
     *
     * @param $value data type is string/array. This is used to add value/array in the current Array.
     * @return void
     */
    public function addValue($value)
    {
        if ($this->currentObject->mode !== 'Array') {
            throw new Exception('Mode should be Array');
        }
        $this->encode($value);
    }

    /**
     * Add simple array/value as in the json format.
     *
     * @param string $key   key of associative array
     * @param        $value data type is string/array. This is used to add value/array in the current Array.
     * @return void
     */
    public function addKeyValue($key, $value)
    {
        if ($this->currentObject->mode !== 'Assoc') {
            throw new Exception('Mode should be Assoc');
        }
        $this->write($this->currentObject->comma);
        $this->write($this->escape($key) . ':');
        $this->currentObject->comma = '';
        $this->encode($value);
    }

    /**
     * Start simple array
     *
     * @param string $key Used while creating simple array inside an associative array and $key is the key.
     * @return void
     */
    public function startArray($key = null)
    {
        if ($this->currentObject) {
            $this->write($this->currentObject->comma);
            array_push($this->objects, $this->currentObject);
        }
        $this->currentObject = new JsonEncoderObject('Array');
        if (!is_null($key)) {
            $this->write($this->escape($key) . ':');
        }
        $this->write('[');
    }

    /**
     * End simple array
     *
     * @return void
     */
    public function endArray()
    {
        $this->write(']');
        $this->currentObject = null;
        if (count($this->objects)>0) {
            $this->currentObject = array_pop($this->objects);
            $this->currentObject->comma = ',';
        }
    }

    /**
     * Start simple array
     *
     * @param string $key Used while creating associative array inside an associative array and $key is the key.
     * @return void
     */
    public function startAssoc($key = null)
    {
        if ($this->currentObject) {
            $this->write($this->currentObject->comma);
            array_push($this->objects, $this->currentObject);
        }
        $this->currentObject = new JsonEncoderObject('Assoc');
        if (!is_null($key)) {
            $this->write($this->escape($key) . ':');
        }
        $this->write('{');
    }

    /**
     * End associative array
     *
     * @return void
     */
    public function endAssoc()
    {
        $this->write('}');
        $this->currentObject = null;
        if (count($this->objects)>0) {
            $this->currentObject = array_pop($this->objects);
            $this->currentObject->comma = ',';
        }
    }

    /**
     * Stream Json String.
     *
     * @return void
     */
    private function streamJson()
    {
        $str = ob_get_clean();
        if (!empty($str) && ENVIRONMENT === PRODUCTION) {
            $log = [
                'datetime' => date('Y-m-d H:i:s'),
                'input' => HttpRequest::$input,
                'error' => $str
            ];
            Logs::log('error', json_encode($log));
            HttpResponse::return5xx(501, 'Error: Facing server side error with API.');
        } else if (empty($str)) {
            // end the json
            // rewind the temp stream.
            rewind($this->tempStream);
            // stream the temp to output
            $outputStream = fopen("php://output", "w+b");
            stream_copy_to_stream($this->tempStream, $outputStream);
            fclose($outputStream);
        } else {
            echo $str;
        }
    }

    /**
     * Checks json was properly closed.
     *
     * @return void
     */
    public function end()
    {
        while ($this->currentObject && $this->currentObject->mode) {
            switch ($this->currentObject->mode) {
                case 'Array':
                    $this->endArray();
                    break;
                case 'Assoc':
                    $this->endAssoc();
                    break;
            }
        }
        $this->streamJson();
    }

    /** 
     * destruct functipn 
     */ 
    public function __destruct() 
    { 
        $this->end();
        fclose($this->tempStream);
    }
}

/**
 * JSON Object
 *
 * This class is built to help maintain state of simple/associative array
 *
 * @category   Json Encoder Object
 * @package    Microservices
 * @author     Ramesh Narayan Jangid
 * @copyright  Ramesh Narayan Jangid
 * @version    Release: @1.0.0@
 * @since      Class available since Release 1.0.0
 */
class JsonEncoderObject
{
    public $mode = '';
    public $comma = '';

    /**
     * Constructor
     *
     * @param string $mode Values can be one among Array/Assoc
     */
    public function __construct($mode)
    {
        $this->mode = $mode;
    }
}

/**
 * Loading JSON class
 *
 * This class is built to handle JSON Object.
 *
 * @category   Json Encoder Object handler
 * @package    Microservices
 * @author     Ramesh Narayan Jangid
 * @copyright  Ramesh Narayan Jangid
 * @version    Release: @1.0.0@
 * @since      Class available since Release 1.0.0
 */
class JsonEncode
{
    /**
     * JSON generator object
     */
    public static $jsonObj = null;

    /**
     * Initialize object
     *
     * @return void
     */
    public static function init()
    {
        self::$jsonObj = new JsonEncoder();
    }

    /**
     * JSON generator object
     *
     * @return object
     */
    public static function getObject()
    {
        if (is_null(self::$jsonObj)) {
            self::init();
        }
        return self::$jsonObj;
    }
}
