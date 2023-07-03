<?php
return [
    'all' => [
        'query' => "SELECT count FROM (SELECT 0 as count) as temp WHERE count = 1",
        'where' => [],
        'mode' => 'singleRowFormat',//Multiple rows returned.
        'subQuery' => [
            'GET' => [
                'query' => "
                        SELECT
                            R.route
                        FROM
                            `{$this->globalDB}`.`{$this->execPhpFunc(getenv('links'))}` L
                        LEFT JOIN 
                            `{$this->globalDB}`.`{$this->execPhpFunc(getenv('routes'))}` R ON L.route_id = R.route_id
                        WHERE __WHERE__",
                'where' => [
                    'L.http_id' => ['custom', '1'],
                    'L.group_id' => ['readOnlySession', 'group_id']
                ],
                'mode' => 'multipleRowFormat'//Multiple rows returned.
            ],
            'POST' => [
                'query' => "
                        SELECT
                            R.route
                        FROM
                            `{$this->globalDB}`.`{$this->execPhpFunc(getenv('links'))}` L
                        LEFT JOIN 
                            `{$this->globalDB}`.`{$this->execPhpFunc(getenv('routes'))}` R ON L.route_id = R.route_id
                        WHERE __WHERE__",
                'where' => [
                    'L.http_id' => ['custom', '2'],
                    'L.group_id' => ['readOnlySession', 'group_id']
                ],
                'mode' => 'multipleRowFormat'//Multiple rows returned.
            ],
            'PUT' => [
                'query' => "
                        SELECT
                            R.route
                        FROM
                            `{$this->globalDB}`.`{$this->execPhpFunc(getenv('links'))}` L
                        LEFT JOIN 
                            `{$this->globalDB}`.`{$this->execPhpFunc(getenv('routes'))}` R ON L.route_id = R.route_id
                        WHERE __WHERE__",
                'where' => [
                    'L.http_id' => ['custom', '3'],
                    'L.group_id' => ['readOnlySession', 'group_id']
                ],
                'mode' => 'multipleRowFormat'//Multiple rows returned.
            ],
            'PATCH' => [
                'query' => "
                        SELECT
                            R.route
                        FROM
                            `{$this->globalDB}`.`{$this->execPhpFunc(getenv('links'))}` L
                        LEFT JOIN 
                            `{$this->globalDB}`.`{$this->execPhpFunc(getenv('routes'))}` R ON L.route_id = R.route_id
                        WHERE __WHERE__",
                'where' => [
                    'L.http_id' => ['custom', '4'],
                    'L.group_id' => ['readOnlySession', 'group_id']
                ],
                'mode' => 'multipleRowFormat'//Multiple rows returned.
            ],
            'DELETE' => [
                'query' => "
                        SELECT
                            R.route
                        FROM
                            `{$this->globalDB}`.`{$this->execPhpFunc(getenv('links'))}` L
                        LEFT JOIN 
                            `{$this->globalDB}`.`{$this->execPhpFunc(getenv('routes'))}` R ON L.route_id = R.route_id
                        WHERE __WHERE__",
                'where' => [
                    'L.http_id' => ['custom', '5'],
                    'L.group_id' => ['readOnlySession', 'group_id']
                ],
                'mode' => 'multipleRowFormat'//Multiple rows returned.
            ],
        ],
    ],
    'single' => [
        'query' => "
                SELECT
                    R.route
                FROM
                    `{$this->globalDB}`.`{$this->execPhpFunc(getenv('links'))}` L
                LEFT JOIN 
                    `{$this->globalDB}`.`{$this->execPhpFunc(getenv('routes'))}` R ON L.route_id = R.route_id
                WHERE __WHERE__",
        'where' => [
            'L.http_id' => ['custom', isset($input['uriParams']['http'])?['GET'=>1,'POST'=>2,'PUT'=>3,'PATCH'=>4,'DELETE'=>5,][$input['uriParams']['http']]:0],
            'L.group_id' => ['readOnlySession', 'group_id']
        ],
        'mode' => 'multipleRowFormat'//Multiple rows returned.
    ]
][isset($input['uriParams']['http'])?'single':'all'];
