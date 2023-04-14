<?php
// Load Payload
parse_str(file_get_contents('php://input'), $payload);

$query = 'UPDATE `{$uriParams['table']}` SET __COLS__ WHERE id = :id';

$config = [
    'sqlArguments' => [
        'required' => ['id'],
        'optional' => []
    ]
];

$REQUEST = array_merge($_POST, $_GET, $uriParameters);

$sqlParams = [];
$sqlPayload = [];
if (count($config['sqlArguments']['required'])) {
    foreach($config['sqlArguments']['required'] as &$value) {
        if (empty($REQUEST[$value])) {
            return404("Missing required parameter '$value'");
        }
        $sqlParams[] = $value;
        $sqlPayload[":$value"] = $REQUEST[$value];
    }
}
if (count($config['sqlArguments']['optional'])) {
    foreach($config['sqlArguments']['optional'] as &$value) {
        if (isset($REQUEST[$value])) {
            $sqlParams[] = $value;
            $sqlPayload[":$value"] = $REQUEST[$value];
        }
    }
}

$colsArray = [];
if (($i_count = count($sqlParams)) > 0) {
    for($i = 0; $i < $i_count; $i++) {
        $colsArray[] = $sqlParams[$i] . ' = :' . $sqlParams[$i];
    }
}

$COLS = '';
if (count($colsArray) > 0) {
    $COLS = implode(' AND ', $colsArray);
}

$query = str_replace('__COLS__', $COLS, $query);

$queries = [
    [$query, $sqlPayload]
];
