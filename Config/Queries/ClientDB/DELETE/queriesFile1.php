<?php
$config = [
    'sqlArguments' => [
        'required' => ['id'],
        'optional' => []
    ]
];

$sqlParams = [];
$sqlPayload = [];

if (count($config['sqlArguments']['required'])) {
    foreach($config['sqlArguments']['required'] as &$value) {
        if (empty($uriParameters[$value])) {
            return404("Missing required parameter '$value'");
        }
        $sqlParams[] = $value;
        $sqlPayload[":$value"] = $uriParameters[$value];
    }
}

$query = 'UPDATE `$uriParams['table']` SET isDeleted = 1 WHERE id = :id';

$queries = [
    [$query, $sqlPayload],
];
