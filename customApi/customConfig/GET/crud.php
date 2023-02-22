<?php
// Available variables $uriParameters,
$query = "SELECT * FROM {$uriParameters['table']} __WHERE__ __ORDER__;";
$query = "SELECT * FROM link_crud_http __WHERE__ __ORDER__;";

if (!empty($uriParameters['id'])) {
    $config = [
        'sqlArguments' => [
            'required' => ['id'],
            'optional' => []
        ]
    ];
} else {
    $config = [
        'sqlArguments' => [
            'required' => [],
            'optional' => []
        ]
    ];
}
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
if (count($config['sqlArguments']['optional'])) {
    foreach($config['sqlArguments']['optional'] as &$value) {
        if (isset($uriParameters[$value])) {
            $sqlParams[] = $value;
            $sqlPayload[":$value"] = $uriParameters[$value];
        }
    }
}

$whereClauseArray = [];
if (($i_count = count($sqlParams)) > 0) {
    for($i = 0; $i < $i_count; $i++) {
        $whereClauseArray[] = $sqlParams[$i] = ':' . $sqlParams[$i];
    }
}
$whereClause = '';
if (count($whereClauseArray) > 0) {
    $whereClause = 'WHERE ' . implode(' AND ', $whereClauseArray);
}
$orderByClause = '';
if (isset($uriParameters['orderBy'])) {
    $orderByClause = "ORDER BY :{$uriParameters['orderBy']} " . (isset($uriParameters['AscDsc']) ? ' ' . $uriParameters['AscDsc'] : ' ASC');
}
$query = str_replace('__WHERE__', $whereClause, $query);
$query = str_replace('__ORDER__', $orderByClause, $query);
if (!empty($_GET['page']) && !empty($_GET['perpage'])) {
    $query .= ' LIMIT :start , :perpage';
    $sqlPayload[':start'] = (int)(($_GET['page'] - 1) * $_GET['perpage']);
    $sqlPayload[':perpage'] = (int)$_GET['perpage'];
}
$queries = [
    'default' => [$query, $sqlPayload, 'singleRowFormat'],
];
