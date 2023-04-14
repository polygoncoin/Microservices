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
 * Loading database server
 *
 * This class is built to handle loading the database server.
 *
 * @category   Cache
 * @package    Microservices
 * @author     Ramesh Narayan Jangid
 * @copyright  Ramesh Narayan Jangid
 * @version    Release: @1.0.0@
 * @since      Class available since Release 1.0.0
 */
class Database
{
    /**
     * Database master hostname
     *
     * @var string
     */
    private $masterHostname = null;

    /**
     * Database master username
     *
     * @var string
     */
    private $masterUsername = null;

    /**
     * Database master password
     *
     * @var string
     */
    private $masterPassword = null;

    /**
     * Database master database
     *
     * @var string
     */
    private $masterDatabase = null;

    /**
     * Database slave hostname
     *
     * @var string
     */
    private $slaveHostname = null;

    /**
     * Database slave username
     *
     * @var string
     */
    private $slaveUsername = null;

    /**
     * Database slave password
     *
     * @var string
     */
    private $slavePassword = null;

    /**
     * Database slave database
     *
     * @var string
     */
    private $slaveDatabase = null;

    /**
     * Database master object
     *
     * @var object
     */
    public $master = null;

    /**
     * Database slave object
     *
     * @var object
     */
    public $slave = null;

    /**
     * Database constructor
     */
    function __construct()
    {
        $this->masterHostname = $_SESSION['DbMasterHostname'];
        $this->masterUsername = $_SESSION['DbMasterUsername'];
        $this->masterPassword = $_SESSION['DbMasterPassword'];
        $this->masterDatabase = $_SESSION['DbMasterDatabase'];

        $this->slaveHostname = $_SESSION['DbSlaveHostname'];
        $this->slaveUsername = $_SESSION['DbSlaveUsername'];
        $this->slavePassword = $_SESSION['DbSlavePassword'];
        $this->slaveDatabase = $_SESSION['DbSlaveDatabase'];
    }

    /**
     * Database connection
     *
     * @param string $mode Can be one of string among master/slave
     * @return void
     */
    function connect($mode)
    {
        if (!is_null($this->$mode)) return;

        $hostname = $mode.'Hostname';
        $username = $mode.'Username';
        $password = $mode.'Password';
        $database = $mode.'Database';

        try {
            $this->$mode = new \PDO(
                "mysql:host={$this->hostname};dbname={$this->database}",
                $this->username,
                $this->password,
                [
                    \PDO::ATTR_EMULATE_PREPARES => false,
                    \PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => false
                ]
            );
        } catch (\PDOException $e) {
            HttpErrorResponse::return501('Unable to connect to database server');
        }
    }
}
