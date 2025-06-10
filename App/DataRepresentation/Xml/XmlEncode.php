<?php
namespace Microservices\App\DataRepresentation\Xml;

use Microservices\App\DataRepresentation\AbstractDataEncode;
use Microservices\App\HttpStatus;

/**
 * Generates Xml
 *
 * @category   Xml Encoder
 * @package    Microservices
 * @author     Ramesh Narayan Jangid
 * @copyright  Ramesh Narayan Jangid
 * @version    Release: @1.0.0@
 * @since      Class available since Release 1.0.0
 */
class XmlEncode extends AbstractDataEncode
{
    /**
     * Temporary Stream
     *
     * @var null|resource
     */
    private $tempStream = null;

    /**
     * Array of XmlEncoderObject objects
     *
     * @var XmlEncoderObject[]
     */
    private $objects = [];

    /**
     * Current XmlEncoderObject object
     *
     * @var null|XmlEncoderObject
     */
    private $currentObject = null;

    /**
     * XmlEncode constructor
     *
     * @param resource $tempStream
     */
    public function __construct(&$tempStream)
    {
        $this->tempStream = &$tempStream;
    }

    /**
     * Write to temporary stream
     *
     * @param string $data Representation Data
     * @return void
     */
    private function write($data)
    {
        fwrite($this->tempStream, $data);
    }

    /**
     * Encodes both simple and associative array to Xml
     *
     * @param string|array $data Representation Data
     * @return void
     */
    public function encode($data)
    {
        if ($this->currentObject) {
            $this->write($this->currentObject->comma);
        }
        if (is_array($data)) {
            $this->write(json_encode($data));
        } else {
            $this->write($this->escape($data));
        }
        if ($this->currentObject) {
            $this->currentObject->comma = ',';
        }
    }

    /**
     * Escape the Xml string value
     *
     * @param null|string $data Representation Data
     * @return string
     */
    private function escape($data)
    {
        if (is_null($data)) return 'null';
        $data = htmlspecialchars($data);
        return "\"{$data}\"";
    }

    /**
     * Append raw Xml string
     *
     * @param string $data Reference of Representation Data
     * @return void
     */
    public function appendData(&$data)
    {
        if ($this->currentObject) {
            $this->write($this->currentObject->comma);
            $this->write($data);
            $this->currentObject->comma = ',';
        }
    }

    /**
     * Append raw Xml string
     *
     * @param string $key  key of associative array
     * @param string $data Reference of Representation Data
     * @return void
     */
    public function appendKeyData($key, &$data)
    {
        if ($this->currentObject && $this->currentObject->mode === 'Object') {
            $this->write($this->currentObject->comma);
            $this->write($this->escape($key) . ':' . $data);
            $this->currentObject->comma = ',';
        }
    }

    /**
     * Add simple array/value as in the Xml format
     *
     * @param string|array $data Representation Data
     * @return void
     * @throws \Exception
     */
    public function addArrayData($data)
    {
        if ($this->currentObject->mode !== 'Array') {
            throw new \Exception('Mode should be Array', HttpStatus::$InternalServerError);
        }
        $this->encode($data);
    }

    /**
     * Add simple array/value as in the Xml format
     *
     * @param string       $key  Key of associative array
     * @param string|array $data Representation Data
     * @return void
     * @throws \Exception
     */
    public function addKeyData($key, $data)
    {
        if ($this->currentObject->mode !== 'Object') {
            throw new \Exception('Mode should be Object', HttpStatus::$InternalServerError);
        }
        $this->write($this->currentObject->comma);
        $this->write($this->escape($key) . ':');
        $this->currentObject->comma = '';
        $this->encode($data);
    }

    /**
     * Start simple array
     *
     * @param null|string $key Used while creating simple array inside an associative array and $key is the key
     * @return void
     */
    public function startArray($key = null)
    {
        if ($this->currentObject) {
            $this->write($this->currentObject->comma);
            array_push($this->objects, $this->currentObject);
        }
        $this->currentObject = new XmlEncoderObject('Array');
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
     * @param null|string $key Used while creating associative array inside an associative array and $key is the key
     * @return void
     * @throws \Exception
     */
    public function startObject($key = null)
    {
        if ($this->currentObject) {
            if ($this->currentObject->mode === 'Object' && is_null($key)) {
                throw new \Exception('Object inside an Object should be supported with a Key', HttpStatus::$InternalServerError);
            }
            $this->write($this->currentObject->comma);
            array_push($this->objects, $this->currentObject);
        }
        $this->currentObject = new XmlEncoderObject('Object');
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
    public function endObject()
    {
        $this->write('}');
        $this->currentObject = null;
        if (count($this->objects)>0) {
            $this->currentObject = array_pop($this->objects);
            $this->currentObject->comma = ',';
        }
    }

    /**
     * Checks Xml was properly closed
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
                case 'Object':
                    $this->endObject();
                    break;
            }
        }
    }
}

/**
 * Xml Object
 *
 * This class is built to help maintain state of simple/associative array
 *
 * @category   Xml Encoder Object
 * @package    Microservices
 * @author     Ramesh Narayan Jangid
 * @copyright  Ramesh Narayan Jangid
 * @version    Release: @1.0.0@
 * @since      Class available since Release 1.0.0
 */
class XmlEncoderObject
{
    public $mode = '';
    public $comma = '';

    /**
     * Constructor
     *
     * @param string $mode Values can be one among Array/Object
     */
    public function __construct($mode)
    {
        $this->mode = $mode;
    }
}
