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
//include method route file.
define('__DOC_ROOT__', realpath(__DIR__ . '/../'));
define('__REQUEST_URI__', trim($_GET['REQUEST_URI'], '/'));
header('Content-Type: application/json; charset=utf-8');

//validate payload params
if (in_array($method, ['POST', 'PUT'])) {
    $payloadKeys = array_keys($payload['required']);
    if (isset($config['payload'])) {
        foreach (array_merge($config['payload']['required'], $config['payload']['optional']) as $value) {
            if (!in_array($value, $payloadKeys)) {
                HttpErrorResponse::return404("Missing required param '$value' in payload");
            } elseif (isset($payload[$value])) {
                $params[':'.$value] = $payload[$value];
            }
        }
    }
}

Init::api();

class validateRequest
{

}