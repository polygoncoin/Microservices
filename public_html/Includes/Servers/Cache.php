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
 * Loading cache server
 *
 * This class is built to handle loading the cache server.
 *
 * @category   Cache
 * @package    Microservices
 * @author     Ramesh Narayan Jangid
 * @copyright  Ramesh Narayan Jangid
 * @license    http://www.php.net/license/3_01.txt  PHP License 3.01
 * @version    Release: @1.0.0@
 * @since      Class available since Release 1.0.0
 */
class Cache
{
    /**
     * Cache master hostname
     *
     * @var string
     */
    private $masterHostname = null;

    /**
     * Cache master password
     *
     * @var string
     */
    private $masterPassword = null;

    /**
     * Cache master port
     *
     * @var int
     */
    private $masterPort = null;

    /**
     * Cache slave hostname
     *
     * @var string
     */
    private $slaveHostname = null;

    /**
     * Cache slave password
     *
     * @var string
     */
    private $slavePassword = null;

    /**
     * Cache slave port
     *
     * @var int
     */
    private $slavePort = null;

    /**
     * Cache master object
     *
     * @var object
     */
    public $master = null;

    /**
     * Cache slave object
     *
     * @var object
     */
    public $slave = null;

    /**
     * Cache constructor
     */
    function __construct()
    {
        $this->masterHostname = $_SESSION['CacheMasterHostname'];
        $this->masterPassword = $_SESSION['CacheMasterPassword'];
        $this->masterPort = $_SESSION['CacheMasterPort'];

        $this->slaveHostname = $_SESSION['CacheSlaveHostname'];
        $this->slavePassword = $_SESSION['CacheSlavePassword'];
        $this->slavePort = $_SESSION['CacheSlavePort'];
    }

    /**
     * Cache connection
     *
     * @param string $mode Can be one of string among master/slave
     * @return void
     */
    function connect($mode)
    {
        if (!is_null($this->$mode)) return;

        $hostname = $mode.'Hostname';
        $password = $mode.'Password';
        $port = $mode.'Port';

        try {
            $this->$mode = new Redis();
            $this->$mode->connect($this->$hostname, $this->$port, 1, NULL, 100);
            $this->$mode->ping();
        } catch (\Exception $e) {
            HttpErrorResponse::return501('Unable to connect to token cache server');
        }
    }
}
