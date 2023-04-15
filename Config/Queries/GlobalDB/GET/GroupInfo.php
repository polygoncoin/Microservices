<?php
/**
 * "default" keyword in below array won't appear in output
 * The query will be executed and ouput will be made avaialable
 * Example:
 * return [
 *     'default' => [
 *         'mode' => 'singleRowFormat'//Single row returned.
 *    ],
 *     'Clients' => [
 *         'mode' => 'multipleRowFormat'//Multiple rows returned.
 *    ],
 *     'Users' => [
 *         'mode' => 'multipleRowFormat'//Multiple rows returned.
 *     ]
 * ];
 * Output:
 * {//singleRowFormat
 *  "key1": "Value1"
 *  "key2": "Value2"
 *  "Clients": [//multipleRowFormat
 *      {
 *          "key1": "Value1"
 *          "key2": "Value2"
 *      },
 *      {
 *          "key1": "Value1"
 *          "key2": "Value2"
 *      }, 
 *  ],
 *  "Users": [//multipleRowFormat
 *      {
 *          "key1": "Value1"
 *          "key2": "Value2"
 *      },
 *      {
 *          "key1": "Value1"
 *          "key2": "Value2"
 *      }, 
 *  ]
 * }
 */
return [
    'default' => [
        'query' => "SELECT id, name FROM {$this->globalDB}.m001_master_group LIMIT 0,1",
        'payload' => [],
        'mode' => 'singleRowFormat'//Single row returned.
    ],
    'Clients' => [
        'query' => "SELECT * FROM {$this->globalDB}.m001_master_group",
        'payload' => [],
        'mode' => 'multipleRowFormat'//Multiple rows returned.
    ],
    'Users' => [
        'query' => "SELECT * FROM {$this->globalDB}.m003_master_user",
        'payload' => [],
        'mode' => 'multipleRowFormat'//Multiple rows returned.
    ]
];
