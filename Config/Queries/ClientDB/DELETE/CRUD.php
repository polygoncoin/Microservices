<?php
return [
    'query' => "UPDATE `{$this->clientDB}`.`{$input['uriParams']['table']}` SET is_deleted = 'Yes' WHERE __WHERE__",
    'where' => [
        'id' => ['uriParams', 'id']
    ]
];
