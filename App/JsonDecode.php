<?php
namespace App;

use App\Logs;
use App\HttpRequest;

/**
 * Creates Objects from JSON String.
 *
 * This class is built to decode large json string or file
 * (which leads to memory limit issues for larger data set)
 * This class gives access to create obects from JSON string
 * in parts for what ever smallest part of data.
 * 
 *
 * @category   JSON Decoder
 * @package    Microservices
 * @author     Ramesh Narayan Jangid
 * @copyright  Ramesh Narayan Jangid
 * @version    Release: @2.0.0@
 * @since      Class available since Release 1.0.0
 */
class JsonDecoder
{
    /**
     * Temporary Stream
     *
     * @var string
     */
    private $tempStream = '';

    /**
     * Array of JsonEncodeObject objects
     *
     * @var array
     */
    private $objects = [];

    /**
     * Parent JsonEncodeObject object
     *
     * @var object
     */
    private $previousObjectIndex = null;

    /**
     * Current JsonEncodeObject object
     *
     * @var object
     */
    private $currentObject = null;

    /**
     * JSON char counter.
     *
     * @var int
     */
    private $jsonCharCounter = null;

    /**
     * Characters that are escaped while creating JSON.
     *
     * @var array
     */
    private $escapers = array("\\", "/", "\"", "\n", "\r", "\t", "\x08", "\x0c", ' ');

    /**
     * Characters that are escaped with for $escapers while creating JSON.
     *
     * @var array
     */
    private $replacements = array("\\\\", "\\/", "\\\"", "\\n", "\\r", "\\t", "\\f", "\\b", ' ');

    private $jsonStreamConfig = null;

    /**
     * JsonEncode constructor
     */
    public function __construct($filepath = "php://input")
    {
        $inputStream = fopen($filepath, "rb");
        $this->tempStream = fopen("php://temp", "rw+b");
        stream_copy_to_stream( $inputStream, $this->tempStream);
        fclose($inputStream);
        rewind($this->tempStream);
        if ($this->interpretJSON()) {
            rewind($this->tempStream);
        }
    }

    /**
     * Validate JSON in stream
     *
     * @return bool
     */
    public function interpretJSON()
    {
        foreach ($this->process(true) as $keys => $val) {
            if (isset($val['__start__'])) {
                $jsonStreamConfig = &$this->jsonStreamConfig;
                for ($i=0, $iCount = count($keys); $i < $iCount; $i++) {
                    if (!isset($jsonStreamConfig[$keys[$i]])) {
                        $jsonStreamConfig[$keys[$i]] = $val;
                        $jsonStreamConfig = &$jsonStreamConfig[$keys[$i]];
                    }
                }    
            }
        }
        echo '<pre>';
        print_r($this->jsonStreamConfig);
        die();
        return true;
    }

    /**
     * Start processing the JSON string
     *
     * @param bool $index Index output.
     * @return void
     */
    public function process($index = false)
    {
        // Flags Variable
        $quote = false;
        $comma = false;
        $colon = false;
        $keyFlag = false;
        $valueFlag = false;

        // Values Variables
        $keyValue = '';
        $valueValue = '';
        $replacements = '';
        $nullStr = null;

        // Variable mode - key/value;
        $varMode = 'keyValue';

        for(;$char = fgetc($this->tempStream);$this->jsonCharCounter++) {
            //Switch mode to value collection after colon.
            if ($quote === false && $colon === false && $char === ':') {
                $colon = true;
                $keyFlag = false;
                $valueFlag = true;
                continue;
            }
            // Start or End of Array or Object.
            if ($quote === false && in_array($char, ['[',']','{','}'])) {
                $arr = $this->handleOpenClose($char, $keyValue, $nullStr, $index);
                if ($arr !== false) {
                    yield $arr['key'] => $arr['value'];
                }
                $keyValue = $valueValue = '';
                $keyFlag = true;
                $valueFlag = false;
                $colon = false;
                continue;
            }
            // Start of Key or value inside quote.
            if ($quote === false && $char === '"') {
                $quote = true;
                if ($colon === true) {
                    $varMode = 'valueValue';
                    $colon = false;
                    $comma = false;
                    $keyFlag = false;
                    $valueFlag = true;
                    continue;
                }
                if ($colon === false) {
                    $varMode = 'keyValue';
                    $keyFlag = true;
                    $valueFlag = false;
                    continue;
                }
            }
            // Escaped values.
            if ($quote === true && $replacements === "\\" && in_array($char, $this->escapers)) {
                $replacement .= $char;
                $$varMode .= str_replace($this->replacements, $this->escapers, $replacement);
                $replacement = '';
                continue;
            }
            // Closing double quotes.
            if ($quote === true && $char === '"') {
                $quote = false;
                $colon = false;
                if ($valueFlag === true) {
                    $this->currentObject->assocValues[$keyValue] = $valueValue;
                    $keyValue = $valueValue = '';
                    $keyFlag = true;
                    $valueFlag = false;
                    $colon = false;
                    $comma = true;
                }
                continue;
            }
            // Collect values for key or value.
            if ($quote === true) {
                $$varMode .= $char;
                continue;
            }
            // Check for null values.
            if ($quote === false) {
                if ($char === ',') {
                    if (!is_null($nullStr)) {
                        $nullStr = $this->checkNullStr($nullStr);
                        switch ($this->currentObject->mode) {
                            case 'Array':
                                $this->currentObject->arrayValues[] = $nullStr;
                                break;
                            case 'Assoc':
                                if (!empty($keyValue)) {
                                    $this->currentObject->assocValues[$keyValue] = $nullStr;
                                }
                                break;
                        }
                        $nullStr = null;
                        $keyValue = $valueValue = '';
                        $keyFlag = true;
                        $valueFlag = false;
                        $colon = false;
                        $comma = true;
                    }
                } else {
                    if (!in_array($char, $this->escapers)) {
                        $nullStr .= $char;
                    }
                }
                continue;
            }
        }
    }

    /**
     * Handles array / object open close char
     *
     * @param string $char     Character among any one "[" "]" "{" "}".
     * @param string $keyValue String value of key of an object.
     * @param string $nullStr  String present in JSON without double quotes.
     * @param bool   $index    Index output.
     * @return array
     */
    private function handleOpenClose(&$char, &$keyValue, &$nullStr, &$index)
    {
        $arr = false;
        switch ($char) {
            case '[':
                $arr = [
                    'key' => $this->keys($index),
                    'value' => $this->getAssocValues()
                ];    
                $this->startArray($keyValue);
                $this->increment();
                break;
            case '{':
                $arr = [
                    'key' => $this->keys($index),
                    'value' => $this->getAssocValues()
                ];
                $this->startAssoc($keyValue);
                $this->increment();
                break;
            case ']':
                if (!empty($keyValue)) {
                    switch ($this->currentObject->mode) {
                        case 'Array':
                            $this->currentObject->arrayValues[] = $keyValue;
                            break;
                        case 'Assoc':
                            if (!empty($keyValue)) {
                                $nullStr = $this->checkNullStr($nullStr);
                                $this->currentObject->assocValues[$keyValue] = $nullStr;
                            }
                            break;
                    }    
                }
                if (count($this->currentObject->arrayValues) > 0) {
                    if ($index) {
                        $arr = [
                            'key' => $this->keys($index),
                            'value' => [
                                '__start__' => $this->currentObject->startCharPosition,
                                '__end__' => $this->jsonCharCounter
                            ]
                        ];
                    } else {
                        $arr = [
                            'key' => $this->keys(),
                            'value' => $this->currentObject->arrayValues
                        ];
                    }
                    $this->currentObject->arrayValues = [];
                }
                $this->endArray();
                break;
            case '}':
                if (!empty($keyValue)) {
                    switch ($this->currentObject->mode) {
                        case 'Array':
                            $this->currentObject->arrayValues[] = $keyValue;
                            break;
                        case 'Assoc':
                            $nullStr = $this->checkNullStr($nullStr);
                            $this->currentObject->assocValues[$keyValue] = $nullStr;
                            break;
                    }    
                }
                if (count($this->currentObject->assocValues) > 0) {
                    if ($index) {
                        $arr = [
                            'key' => $this->keys($index),
                            'value' => [
                                '__start__' => $this->currentObject->startCharPosition,
                                '__end__' => $this->jsonCharCounter
                            ]
                        ];
                    } else {
                        $arr = [
                            'key' => $this->keys(),
                            'value' => $this->currentObject->assocValues
                        ];
                    }
                    $this->currentObject->assocValues = [];
                }
                $this->endAssoc();
                break;
        }
        if (
            $arr !== false && 
            isset($arr['value']) && 
            $arr['value'] !== false && 
            count($arr['value']) > 0
        ) {
            return $arr;
        }
        return false;
    }

    /**
     * Check String present in JSON without double quotes for null or integer
     *
     * @param string $nullStr String present in JSON without double quotes.
     * @return mixed
     */
    private function checkNullStr($nullStr)
    {
        $return = false;
        if ($nullStr === 'null') {
            $return = null;
        } elseif (is_numeric($nullStr)) {
            $return = (int)$nullStr;
        }
        if ($return === false) {
            $this->isBadJson($nullStr);
        }
        return $return;
    }

    /**
     * Start of array
     *
     * @param string $key Used while creating simple array inside an associative array and $key is the key.
     * @return void
     */
    private function startArray($key = null)
    {
        if ($this->currentObject) {
            if ($this->currentObject->mode === 'Assoc' && empty(trim($key))) {
                $this->isBadJson($key);
            }
            if ($this->currentObject->mode === 'Array' && !empty(trim($key))) {
                $this->isBadJson($key);
            }
            array_push($this->objects, $this->currentObject);
            $this->currentObject = null;
        }
        if (count($this->objects) > 0) {
            $this->previousObjectIndex = count($this->objects) - 1;
        } else {
            $this->previousObjectIndex = null;
        }
        $this->currentObject = new JsonDecoderObject('Array', $key);
        $this->currentObject->startCharPosition = $this->jsonCharCounter;
    }

    /**
     * End of array
     *
     * @return void
     */
    private function endArray()
    {
        if ($this->currentObject) {
            $this->currentObject->endCharPosition = $this->jsonCharCounter;
        }
        if (count($this->objects) > 0) {
            $this->currentObject = array_pop($this->objects);
        }
        if (count($this->objects) > 0) {
            $this->previousObjectIndex = count($this->objects) - 1;
        } else {
            $this->previousObjectIndex = null;
        }
    }

    /**
     * Start of object
     *
     * @param string $key Used while creating associative array inside an associative array and $key is the key.
     * @return void
     */
    private function startAssoc($key = null)
    {
        if ($this->currentObject) {
            if ($this->currentObject->mode === 'Assoc' && empty(trim($key))) {
                $this->isBadJson($key);
            }
            if ($this->currentObject->mode === 'Array' && !empty(trim($key))) {
                $this->isBadJson($key);
            }
            array_push($this->objects, $this->currentObject);
            $this->currentObject = null;
        }
        if (count($this->objects) > 0) {
            $this->previousObjectIndex = count($this->objects) - 1;
        } else {
            $this->previousObjectIndex = null;
        }
        $this->currentObject = new JsonDecoderObject('Assoc', $key);
        $this->currentObject->startCharPosition = $this->jsonCharCounter;
    }

    /**
     * End of object
     *
     * @return void
     */
    private function endAssoc()
    {
        if ($this->currentObject) {
            $this->currentObject->endCharPosition = $this->jsonCharCounter;
        }
        if (count($this->objects) > 0) {
            $this->currentObject = array_pop($this->objects);
        }
        if (count($this->objects) > 0) {
            $this->previousObjectIndex = count($this->objects) - 1;
        } else {
            $this->previousObjectIndex = null;
        }
    }

    /**
     * Increment arrayKey counter for array of objects or arrays
     *
     * @return void
     */
    private function increment()
    {
        if (
            !is_null($this->previousObjectIndex) &&
            $this->objects[$this->previousObjectIndex]->mode === 'Array'
        ) {
            if (is_null($this->objects[$this->previousObjectIndex]->arrayKey)) {
                $this->objects[$this->previousObjectIndex]->arrayKey = 0;
            } else {
                $this->objects[$this->previousObjectIndex]->arrayKey++;
            }
        }
    }

    /**
     * Returns extracted object values.
     *
     * @return array
     */
    private function getAssocValues()
    {
        $arr = false;
        if (
            !is_null($this->currentObject) && 
            $this->currentObject->mode === 'Assoc' && 
            count($this->currentObject->assocValues) > 0
        ) {
            $arr = $this->currentObject->assocValues;
            $this->currentObject->assocValues = [];
        }
        return $arr;
    }

    /**
     * Check for a valid JSON.
     * 
     * @return void
     */
    private function isBadJson($str)
    {
        $str = trim($str);
        if (!empty($str)) {
            echo 'Invalid JSON: '.$str;
            die;    
        }
    }

    /**
     * Generated Array.
     * 
     * @param bool $index Index output.
     * @return array
     */
    private function keys($index = false)
    {
        $keys = [];
        $return = &$keys;
        $objCount = count($this->objects);
        if ($objCount > 0) {
            for ($i=1; $i<$objCount; $i++) {
                switch ($this->objects[$i]->mode) {
                    case 'Assoc':
                        if (!is_null($this->objects[$i]->assocKey)) {
                            if ($index) {
                                $keys[] = $this->objects[$i]->assocKey;    
                            } else {
                                $keys[$this->objects[$i]->assocKey] = [];
                                $keys = &$keys[$this->objects[$i]->assocKey];    
                            }
                        }
                        break;
                    case 'Array':
                        if (!is_null($this->objects[$i]->assocKey)) {
                            if ($index) {
                                $keys[] = $this->objects[$i]->assocKey;
                            } else {
                                $keys[$this->objects[$i]->assocKey] = [];
                                $keys = &$keys[$this->objects[$i]->assocKey];
                            }
                        }
                        if (!is_null($this->objects[$i]->arrayKey)) {
                            if ($index) {
                                $keys[] = $this->objects[$i]->arrayKey;
                            } else {
                                $keys[$this->objects[$i]->arrayKey] = [];
                                $keys = &$keys[$this->objects[$i]->arrayKey];
                            }
                        }
                        break;
                }
            }
        }
        if ($this->currentObject) {
            switch ($this->currentObject->mode) {
                case 'Assoc':
                    if (!is_null($this->currentObject->assocKey)) {
                        if ($index) {
                            $keys[] = $this->currentObject->assocKey;
                        } else {
                            $keys[$this->currentObject->assocKey] = [];
                            $keys = &$keys[$this->currentObject->assocKey];    
                        }
                    }
                    break;
                case 'Array':
                    if (!is_null($this->currentObject->assocKey)) {
                        if ($index) {
                            $keys[] = $this->currentObject->assocKey;
                        } else {
                            $keys[$this->currentObject->assocKey] = [];
                            $keys = &$keys[$this->currentObject->assocKey];    
                        }
                    }
                    if (!is_null($this->currentObject->arrayKey)) {
                        if ($index) {
                            $keys[] = $this->currentObject->arrayKey;
                        } else {
                            $keys[$this->currentObject->arrayKey] = [];
                            $keys = &$keys[$this->currentObject->arrayKey];
                        }
                    }
                    break;
            }    
        }
        return $return;
    }
}

/**
 * JSON Object
 *
 * This class is built to help maintain state of an Array or Object of JSON.
 *
 * @category   Json Decoder Object
 * @package    Microservices
 * @author     Ramesh Narayan Jangid
 * @copyright  Ramesh Narayan Jangid
 * @version    Release: @1.0.0@
 * @since      Class available since Release 1.0.0
 */
class JsonDecoderObject
{
    /**
     * JSON char start position.
     *
     * @var int
     */
    public $startCharPosition = null;

    /**
     * JSON char end position.
     *
     * @var int
     */
    public $endCharPosition = null;
    
    /**
     * Assoc / Array.
     *
     * @var string
     */
    public $mode = '';

    /**
     * Assoc key for parant object.
     *
     * @var string
     */
    public $assocKey = null;
    
    /**
     * Array key for parant object.
     *
     * @var string
     */
    public $arrayKey = null;

    /**
     * Assoc values.
     *
     * @var array
     */
    public $assocValues = [];

    /**
     * Array values.
     *
     * @var array
     */
    public $arrayValues = [];

    /**
     * Constructor
     *
     * @param string $mode Values can be one among Array/Assoc
     */
    public function __construct($mode, $assocKey = null)
    {
        $this->mode = $mode;
        $assocKey = trim($assocKey);
        $this->assocKey = !empty($assocKey) ? $assocKey : null;
    }
}

/**
 * Loading JSON class
 *
 * This class is built to handle JSON Object.
 *
 * @category   Json Decoder Object handler
 * @package    Microservices
 * @author     Ramesh Narayan Jangid
 * @copyright  Ramesh Narayan Jangid
 * @version    Release: @1.0.0@
 * @since      Class available since Release 1.0.0
 */
class JsonDecode
{
    /**
     * JSON generator object
     */
    public static $jsonDecoderObj = null;

    /**
     * Initialize object
     *
     * @return void
     */
    public static function init()
    {
        self::$jsonDecoderObj = new JsonDecoder();
    }

    /**
     * JSON generator object
     *
     * @return object
     */
    public static function getObject()
    {
        if (is_null(self::$jsonDecoderObj)) {
            self::init();
        }
        return self::$jsonDecoderObj;
    }
}
