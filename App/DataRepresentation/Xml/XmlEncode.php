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
        $xml = '<?xml version="1.0" encoding="UTF-8"?>';
        $this->write($xml);
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
        if (is_array($data)) {
            $isAssoc = (isset($data[0])) ? false : true;
            if (!$isAssoc) {
                $this->write("<Rows>");    
            }
            $this->write("<Row>");
            foreach ($data as $key => $value) {
                if (!is_array($value)) {
                    $this->write("<field name=\"{$this->escape($key)}\">{$this->escape($value)}</field>");
                } else {
                    $this->addKeyData($key, $value);
                }
            }
            $this->write("</Row>");
            if (!$isAssoc) {
                $this->write("</Rows>");    
            }
        } else {
            $this->write($this->escape($data));
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
        return htmlspecialchars($data);
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
            $this->write($data);
        }
    }

    /**
     * Append raw Xml string
     *
     * @param string $tag  Tag of associative array
     * @param string $data Reference of Representation Data
     * @return void
     */
    public function appendKeyData($tag, &$data)
    {
        if ($this->currentObject && $this->currentObject->mode === 'Object') {
            $this->write("<{$tag}>{$this->escape($data)}</{$tag}>");
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
     * @param string       $tag  Tag of associative array
     * @param string|array $data Representation Data
     * @return void
     * @throws \Exception
     */
    public function addKeyData($tag, $data)
    {
        if ($this->currentObject->mode !== 'Object') {
            throw new \Exception('Mode should be Object', HttpStatus::$InternalServerError);
        }
        $this->write("<{$tag}>");
        $this->encode($data);
        $this->write("</{$tag}>");
    }

    /**
     * Start simple array
     *
     * @param null|string $tag Used while creating simple array inside an associative array and $tag is the key
     * @return void
     */
    public function startArray($tag = null)
    {
        if (is_null($tag)) {
            $tag = 'Rows';
        }
        if ($this->currentObject) {
            array_push($this->objects, $this->currentObject);
        }
        $this->currentObject = new XmlEncoderObject('Array', $tag);
        $this->write("<{$tag}>");
    }

    /**
     * End simple array
     *
     * @return void
     */
    public function endArray()
    {
        $this->write("</{$this->currentObject->tag}>");
        $this->currentObject = null;
        if (count($this->objects)>0) {
            $this->currentObject = array_pop($this->objects);
        }
    }

    /**
     * Start simple array
     *
     * @param null|string $tag Used while creating associative array inside an associative array and $tag is the key
     * @return void
     * @throws \Exception
     */
    public function startObject($tag = null)
    {
        if (is_null($tag)) {
            $tag = 'Resultset';
        }
        if ($this->currentObject) {
            if ($this->currentObject->mode === 'Object' && is_null($tag)) {
                throw new \Exception('Object inside an Object should be supported with a Key', HttpStatus::$InternalServerError);
            }
            array_push($this->objects, $this->currentObject);
        }
        $this->currentObject = new XmlEncoderObject('Object', $tag);
        $this->write("<{$tag}>");
    }

    /**
     * End associative array
     *
     * @return void
     */
    public function endObject()
    {
        $this->write("</{$this->currentObject->tag}>");
        $this->currentObject = null;
        if (count($this->objects)>0) {
            $this->currentObject = array_pop($this->objects);
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
    public $tag = '';

    /**
     * Constructor
     *
     * @param string $mode Values can be one among Array/Object
     */
    public function __construct($mode, $tag)
    {
        $this->mode = $mode;
        $this->tag = $tag;
    }
}
