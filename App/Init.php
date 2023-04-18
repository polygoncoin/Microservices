<?php
namespace App;

use App\JsonEncode;
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
 * Class to initialize api HTTP request
 *
 * This class process the api request
 *
 * @category   Cache
 * @package    Microservices
 * @author     Ramesh Narayan Jangid
 * @copyright  Ramesh Narayan Jangid
 * @version    Release: @1.0.0@
 * @since      Class available since Release 1.0.0
 */
class Init extends Authorize
{
    public static function api()
    {
        parent::init();

        switch ($method) {
            case 'GET':
                self::processHttpGET();
                break;
            case 'POST':
                self::processHttpPOST();
                break;
            case 'PUT':
                self::processHttpPUT();
                break;
            case 'PATCH':
                self::processHttpPATCH();
                break;
            case 'DELETE':
                self::processHttpDELETE();
                break;
        }
    }

    /**
     * Process HTTP GET request
     *
     * @return void
     */
    function processHttpGET()
    {
        // Load uriParams
        $uriParams = $this->routeParams;

        // Load Read Only Session
        $readOnlySession = $this->readOnlySession;

        // Load Queries
        $queries = include $this->__file__;

        $jsonEncode = new JsonEncode();
        if (isset($queries['default'])) {
            $sth = $this->conn->select($queries['default'][0]);
            $sth->execute($queries['default'][1]);
            if ($queries['default'][2] === 'singleRowFormat') {
                $jsonEncode->startAssoc();
                foreach($sth->fetch(PDO::FETCH_ASSOC) as $key => $value) {
                    $jsonEncode->addKeyValue($key, $value);
                }
                $sth->closeCursor();
            }
            if ($queries['default'][2] === 'multipleRowFormat') {
                $jsonEncode->startArray();
                for (;$row=$sth->fetch(PDO::FETCH_ASSOC);) {
                    $jsonEncode->encode($row);
                }
                $jsonEncode->endArray();
                $sth->closeCursor();
                return;
            }
        }
        if (isset($queries['default'][2]) && $queries['default'][2] === 'singleRowFormat') {
            foreach ($queries as $key => &$value) {
                if ($key === 'default') continue;
                $sth = $this->conn->select($value[0]);
                $sth->execute($value[1]);
                if ($queries[$key][2] === 'singleRowFormat') {
                    $jsonEncode->addKeyValue($key, $sth->fetch(PDO::FETCH_ASSOC));
                }
                if ($queries[$key][2] === 'multipleRowFormat') {
                    $jsonEncode->startArray($key);
                    $jsonEncode->addValue($sth->fetch(PDO::FETCH_ASSOC));
                    $jsonEncode->endArray($key);
                }
                $sth->closeCursor();
            }
            $jsonEncode->endAssoc();
        }
        $jsonEncode = null;
    }

    /**
     * Process HTTP POST request
     *
     * @return void
     */
    function processHttpPOST()
    {
        // Load uriParams
        $uriParams = $this->routeParams;

        // Load Read Only Session
        $readOnlySession = $this->readOnlySession;

        // Load Queries
        $queries = include $this->__file__;

        foreach ($queries as $key => &$value) {
            $sth = $this->conn->insert($value[0]);
            $sth->execute($value[1]);
            $result[$key] = $connection->lastInsertId();
            $sth->closeCursor();
        }
    }

    /**
     * Process HTTP PUT request
     *
     * @return void
     */
    function processHttpPUT()
    {
        // Load uriParams
        $uriParams = $this->routeParams;

        // Load Read Only Session
        $readOnlySession = $this->readOnlySession;

        // Load Payload
        parse_str(file_get_contents('php://input'), $payload);

        // Load Queries
        $queries = include $this->__file__;

        foreach ($queries as $key => &$value) {
            $sth = $this->conn->update($value[0]);
            $sth->execute($value[1]);
            $result[$key] = $sth->rowCount();
            $sth->closeCursor();
        }
    }

    /**
     * Process HTTP PATCH request
     *
     * @return void
     */
    function processHttpPATCH()
    {
        // Load uriParams
        $uriParams = $this->routeParams;

        // Load Read Only Session
        $readOnlySession = $this->readOnlySession;

        // Load Payload
        parse_str(file_get_contents('php://input'), $payload);

        // Load Queries
        $queries = include $this->__file__;

        foreach ($queries as $key => &$value) {
            $sth = $this->conn->update($value[0]);
            $sth->execute($value[1]);
            $result[$key] = $sth->rowCount();
            $sth->closeCursor();
        }
    }

    /**
     * Process HTTP DELETE request
     *
     * @return void
     */
    function processHttpDELETE()
    {
        // Load uriParams
        $uriParams = $this->routeParams;

        // Load Read Only Session
        $readOnlySession = $this->readOnlySession;

        // Load Queries
        $queries = include $this->__file__;

        foreach ($queries as $key => &$value) {
            $sth = $this->conn->update($value[0]);
            $sth->execute($value[1]);
            $result[$key] = $sth->rowCount();
            $sth->closeCursor();
        }
    }
}
