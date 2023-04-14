<?php
/*
MIT License 

Copyright (c) 2023 Ramesh Narayan Jangid. 

Permission is hereby granted, free of charge, to any person obtaining a copy 
of this software and associated documentation files (the "Software"), to deal 
in the Software without restriction, including without limitation the rights 
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell 
copies of the Software, and to permit persons to whom the Software is 
furnished to do so, subject to the following conditions: 

The above copyright notice and this permission notice shall be included in all 
copies or substantial portions of the Software. 

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR 
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, 
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE 
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER 
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, 
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE 
SOFTWARE. 
*/ 
/**
 * Creates JSON
 *
 * This class is built to avoid creation of large array objects
 * (which leads to memory limit issues for larger data set)
 * which are then converted to JSON. This class gives access to
 * create JSON in parts for what ever smallest part of data
 * we have of the large data set which are yet to be fetched.
 *
 * @category   JSON
 * @package    Microservices
 * @author     Ramesh Narayan Jangid
 * @copyright  Ramesh Narayan Jangid
 * @version    Release: @1.0.0@
 * @since      Class available since Release 1.0.0
 */ 
 class JsonEncode
{
    /**
     * Array of JsonEncodeObject objects
     *
     * @var array
     */
    private $objects = [];

    /**
     * Current JsonEncodeObject object
     *
     * @var object
     */
    private $currentObject = null;

    /**
     * JsonEncode constructor
     */
    public function __construct()
    {

    }

    /**
     * Escape the json string key or value
     *
     * @param string $str json key or value string.
     * @return string
     */
    private function escape($str)
    {
        $escapers = array("\\", "/", "\"", "\n", "\r", "\t", "\x08", "\x0c");
        $replacements = array("\\\\", "\\/", "\\\"", "\\n", "\\r", "\\t", "\\f", "\\b");
        $str = str_replace($escapers, $replacements, $str);
        return '"' . $str . '"';
    }

    /**
     * Encodes both simple and associative array to json
     *
     * @param $arr string value escaped and array value json_encode function is applied.  
     * @return void
     */
    private function encode(&$arr)
    {
        if (!is_array($arr)) {
            echo $this->escape($arr);
            return;
        }
        echo json_encode($arr);
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
        echo $this->currentObject->comma;
        $this->encode($value);
        $this->currentObject->comma = ',';
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
        echo $this->currentObject->comma;
        echo $this->escape($key) . ':';
        echo $this->encode($value);
        $this->currentObject->comma = ',';
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
            echo $this->currentObject->comma;
            array_push($this->objects, $this->currentObject);
        }
        $this->currentObject = new JsonEncodeObject('Array');
        if (!is_null($key)) {
            echo $this->escape($key) . ':';
        }
        echo '[';
    }

    /**
     * End simple array
     *
     * @return void
     */
    public function endArray()
    {
        echo ']';
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
            echo $this->currentObject->comma;
            array_push($this->objects, $this->currentObject);
        }
        $this->currentObject = new JsonEncodeObject('Assoc');
        if (!is_null($key)) {
            echo $this->escape($key) . ':';
        }
        echo '{';
    }

    /**
     * End associative array
     *
     * @return void
     */
    public function endAssoc()
    {
        echo '}';
        $this->currentObject = null;
        if (count($this->objects)>0) {
            $this->currentObject = array_pop($this->objects);
            $this->currentObject->comma = ',';
        }
    }

    /**
     * Checks json was properly closed.
     *
     * @return void
     */
    public function end()
    {
        if ($this->currentObject || count($this->objects)>0) {
            die('Mismatch in JsonEncode function calls');
        }
    }

    /**
     * destruct functipn
     */
    public function __destruct()
    {
        $this->end();
    }
}

/**
 * JSON Object
 *
 * This class is built to help maintain state of simple/associative array
 *
 * @category   JsonObject
 * @package    Microservices
 * @author     Ramesh Narayan Jangid
 * @copyright  Ramesh Narayan Jangid
 * @version    Release: @1.0.0@
 * @since      Class available since Release 1.0.0
 */
class JsonEncodeObject
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
