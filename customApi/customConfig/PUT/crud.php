<?php
<?php
// Load Payload
parse_str(file_get_contents('php://input'), $payload);

$columnsConfig = [
    'table0' => [
        'payload' => [
            'required' => [],
            'optional' => []
        ]
    ],
]
if (!isset($columnsConfig[$uriParams['table']])) {

}
$query = 'UPDATE `table` SET __COLS__ WHERE id = :id';
$params = [];
