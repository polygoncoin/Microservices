<?php
return [
    'query' => "UPDATE {$this->globalDB}.{$input['uriParams']['table']} SET is_deleted = 'Yes' WHERE __WHERE__",
    'where' => [
        'id' => ['uriParams', 'id']
    ]
];
