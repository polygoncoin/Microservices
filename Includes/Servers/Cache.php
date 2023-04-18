<?php
namespace Includes\Servers;
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
     * Cache hostname
     *
     * @var string
     */
    private $hostname = null;

    /**
     * Cache port
     *
     * @var int
     */
    private $port = null;

    /**
     * Cache password
     *
     * @var string
     */
    private $password = null;

    /**
     * Cache database
     *
     * @var string
     */
    private $database = null;

    /**
     * Cache connection
     *
     * @var object
     */
    private $redis = null;

    /**
     * Cache constructor
     */
    public function __construct($hostname, $port, $password, $database = NULL)
    {
        $this->hostname = getenv($hostname);
        $this->port = getenv($port);
        $this->password = getenv($password);
        if (!empty($database)) {
            $this->database = getenv($database);
        }
    }

    /**
     * Cache connection
     *
     * @param string $mode Can be one of string among master/slave
     * @return void
     */
    function connect()
    {
        if (!is_null($this->redis)) return;
        try {
            $this->redis = new Redis();
            //Connecting to Redis
            $this->redis->connect($this->hostname, $this->port, 1, NULL, 100);
            $this->redis->auth($this->password);
            if (!empty($this->database)) {
                $this->redis->select($this->database);
            }
            if (!$this->redis->ping()) {
                HttpErrorResponse::return501('Unable to ping to cache server');
            }
        } catch (Exception $e) {
            HttpErrorResponse::return501('Unable to connect to cache server');
        }
    }
}
