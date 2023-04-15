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
define('__DOC_ROOT__', realpath(__DIR__ . '/../'));

require_once __DOC_ROOT__ . '/public_html/Includes/HttpRequest.php';
require_once __DOC_ROOT__ . '/public_html/Includes/HttpErrorResponse.php';
require_once __DOC_ROOT__ . '/public_html/Includes/Servers/Cache.php';
require_once __DOC_ROOT__ . '/public_html/Includes/Servers/Database.php';
require_once __DOC_ROOT__ . '/public_html/Includes/Connection.php';
require_once __DOC_ROOT__ . '/public_html/Includes/Authorize.php';
require_once __DOC_ROOT__ . '/public_html/Includes/Init.php';
require_once __DOC_ROOT__ . '/public_html/Includes/JsonEncode.php';

define('__REQUEST_URI__', trim($_GET['REQUEST_URI'], '/'));

header('Content-Type: application/json; charset=utf-8');

Init::api($authirizationHeader, $httpMethod, $requestIP);