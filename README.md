# Microservices!

Hi! This is the first very light and easy **Microservices** package that can be configured very easily and your API is up in few minutes.

## Folders
- **/App** Basic Microservices application folder
- **/Config** Basic Microservices configuration folder
- **/public_html** Microservices doc root folder

## Files
- **/.env.example** Create a copy of this file as **.env**
- **/db.sql** Import this SQL on **MySQL** Server
- **/public_html/index.php** This is the file to be accessed for the API's

## .env

- One needs to set the configurations in the **.env** file. The configurations include Cache/Database creds and other credentials.
- Below are the default server configuration parameters.
	> defaultCacheHostname='127.0.0.1'
	> defaultCachePort=6379
	> defaultCachePassword=''
	> defaultCacheDatabase=0
	> defaultDbHostname='127.0.0.1'
	> defaultDbUsername='username'
	> defaultDbPassword='password'
	> defaultDbDatabase='Database'

If there is a requirement from your client for a **Separate DB or Host** for saving his data, just set these settings here and configure these **.env variables in the global.m004_master_connection table**
- For a different DB on default Host
	> defaultDbDatabase007='DatabaseName007'
- For a different Host/DB instance
	> newDbHostname='hostname'
	> newDbUsername='username'
	> newDbPassword='password'
	> newDbDatabase='Database'

This can extend to any number of databases on the default host or to any number of dedicated hosts for respective clients.

**Note:** 
- The connection configuration used for an API is limited to the respective group. One group access is limited to one connection configuration.
- All the configuration here is with respect to the group. Users, Routes & Connections are configured and grouped inside a **GROUP**

- Any change made to the global database needs to be updated in the cache server. This can be done by accessing **/reload** route. This is restricted by HTTP Authentication and configuration for same can be found in .env as below.
	> HttpAuthenticationRestrictedIp='127.0.0.1'
	> HttpAuthenticationUser='username'
	> HttpAuthenticationPassword='password'

## Configuring route
### Files
- **/Config/Routes/GETroutes.php** GET method routes.
- **/Config/Routes/POSTroutes.php** POST method routes.
- **/Config/Routes/PUTroutes.php** PUT method routes.
- **/Config/Routes/PATCHroutes.php** PATCH method routes.
- **/Config/Routes/DELETEroutes.php** DELETE method routes.
- For configuring route **/global/tableName/parts** GET method
```
	'global' => [
		'tableName' => [
			'parts' => [
				'__file__' => 'SQL file location'
			]
		]
	];
```
- For configuring route **/global/tableName/{id}** where id is dynamic **integer** value to be collected.
```
	'global' => [
		'tableName' => [
			'{id:int}' => [
				'__file__' => 'SQL file location'
			]
		]
	];
```
- Same dynamic variable but with a different data type, for e.g. **{id}** will be treated differently for **string** and **integer** values to be collected.
```
	'global' => [
		'tableName' => [
			'{id:int}' => [
				'__file__' => 'SQL file location for integer data type'
			]
			'{id:string}' => [
				'__file__' => 'SQL file location for string data type'
			]
		]
	];
```
- Suppose you want to restrict dynamic values to a certain set of values. One can do the same by appending comma-separated values after OR key.
```
	'global' => [
		'{tableName:string|admin,group,client,routes}' => [
			'{id:int}' => [
				'__file__' => 'SQL file location'
			]
		]
	];
```
## Configuring Route in Database
Suppose we want to configure the below 2 routes for our application.
- /global/{table:string}
- /global/{table:string}/{id:int}

Lets discuss the process for each
- **/global/{table:string}**
 	> **table** is the dynamic string
```
INSERT INTO  `m003_master_route`(`route`)  VALUES ('/global/{table:string}');
```
After inserting one needs to configure this route for use.
To configure for GET, and POST methods.
```
INSERT INTO
	`l001_link_allowed_route`
SET
	`group_id` = 1,
	`client_id` = 1,
	`route_id` = 1, -- Insert id of route query
	`http_id` = 1 -- 1 is for GET method
;
```
```
INSERT INTO
	`l001_link_allowed_route`
SET
	`group_id` = 1,
	`client_id` = 1,
	`route_id` = 1, -- Insert id of route query
	`http_id` = 2 -- 2 is for POST method
;
```
So route /global/{table:string} can be used for adding and fetching table records
- **/global/{table:string}/{id:int}**
 	> **table** is the dynamic string
 	> **id** is the dynamic integer
```
INSERT INTO  `m003_master_route`(`route`)  VALUES ('/global/{table:string}/{id:int}');
```
After inserting one needs to configure this route for use.
To configure for PUT/PATCH/DELETE method.
```
INSERT INTO
	`l001_link_allowed_route`
SET
	`group_id` = 1,
	`client_id` = 1,
	`route_id` = 2, -- Insert id of route query.
	`http_id` = 1 -- 1 is for GET method
;
```
```
INSERT INTO
	`l001_link_allowed_route`
SET
	`group_id` = 1,
	`client_id` = 1,
	`route_id` = 2, -- Insert id of route query.
	`http_id` = 3 -- 3 is for PUT method
;
```
```
INSERT INTO
	`l001_link_allowed_route`
SET
	`group_id` = 1,
	`client_id` = 1,
	`route_id` = 2, -- Insert id of route query.
	`http_id` = 4 -- 4 is for PATCH method
;
```
```
INSERT INTO
	`l001_link_allowed_route`
SET
	`group_id` = 1,
	`client_id` = 1,
	`route_id` = 2, -- Insert id of route query.
	`http_id` = 5 -- 5 is for DELETE method
;
```
So route /global/{table:string}/{id:int} can be used for updating and fetching a specific record of a table.

## Configuring SQL's
### Folder
- **/Config/Queries/GlobalDB** for global database.
- **/Config/Queries/ClientDB** for all client databases including all hosts.


### Files - GlobalDB
- **/Config/Queries/GlobalDB/GET/filename.php** GET method SQL.
- **/Config/Queries/GlobalDB/POST/filename.php** POST method SQL.
- **/Config/Queries/GlobalDB/PUT/filename.php** PUT method SQL.
- **/Config/Queries/GlobalDB/PATCH/filename.php** PATCH method SQL.
- **/Config/Queries/GlobalDB/DELETE/filename.php** DELETE method SQL.

- **/Config/Queries/GlobalDB/** for global database.
hosts.

### Files - ClientDB
- **/Config/Queries/ClientDB/GET/filename.php** GET method SQL.
- **/Config/Queries/ClientDB/POST/filename.php** POST method SQL.
- **/Config/Queries/ClientDB/PUT/filename.php** PUT method SQL.
- **/Config/Queries/ClientDB/PATCH/filename.php** PATCH method SQL.
- **/Config/Queries/ClientDB/DELETE/filename.php** DELETE method SQL.

- **/Config/Queries/ClientDB/** for global database.
hosts.

### SQL's
The supported SQL format are as below
- For GET method.
```
<?php
return [
	'query' => "SELECT  *  FROM {$this->globalDB}.TableName WHERE id = ? AND group_id = ? AND client_id = ?",
	'where' => [
		//column => [uriParams|payload|readOnlySession|{custom} => key|{value}],
		'id' => ['uriParams' => 'id'],
		'group_id' => ['payload' => 'group_id'],
		'client_id' => ['readOnlySession' => 'client_id']
	],
	'mode' => 'singleRowFormat',//Single row returned.
	'subQuery' => [
		'Clients' => [
			'query' => "MySQL Query here",
			'where' => [],
			'mode' => 'multipleRowFormat'//Multiple rows returned.
		],
		'Users' => [
			'query' => "MySQL Query here",
			'where' => [],
			'mode' => 'multipleRowFormat'//Multiple rows returned.
		]
	],
	'validate' => [
		[
			'fn' => 'validateGroupId',
			'val' => ['payload' => 'group_id'],
			'errorMessage' => 'Invalid Group Id'
		],
		[
			'fn' => 'validateClientId',
			'val' => ['payload' => 'client_id'],
			'errorMessage' => 'Invalid Client Id'
		],
	]
];
```
Here **query & mode** keys are required keys
**Note:** For GET method **payload** is query string parameters; basically **$_GET**.
- For POST/PUT/PATCH/DELETE method.
```
<?php
return [
	'query' => "INSERT {$this->globalDB}.TableName SET __SET__ WHERE __WHERE__ ",
	'payload' => [// for __SET__
		//column => [uriParams|payload|readOnlySession|insertIdParams|{custom} => key|{value}],
		'group_id' => ['payload' => 'group_id'],
		'client_id' => ['readOnlySession' => 'client_id']
	],
	'where' => [// for __WHERE__
		//column => [uriParams|payload|readOnlySession|{custom} => key|{value}],
		'id' => ['uriParams' => 'id']
	],
	'insertId' => 'm001_master_group:id',// Last insert id key name in $input['insertIdParams'][key name];
	'subQuery' => [
		[
			'query' => "MySQL Query here",
			'payload' => [
				'previous_table_id' => ['insertIdParams' => 'm001_master_group:id'],
			],
			'where' => [],
		],
		[
			'query' => "MySQL Query here",
			'payload' => [],
			'where' => [],
			'subQuery' => [
				[
					'query' => "MySQL Query here",
					'payload' => [],
					'where' => [],
				],
				[
					'query' => "MySQL Query here",
					'payload' => [],
					'where' => [],
				]
			]
		]
	],
	'validate' => [
		[
			'fn' => 'validateGroupId',
			'val' => ['payload' => 'group_id'],
			'errorMessage' => 'Invalid Group Id'
		],
	]
];
```
Here **query & payload** keys are required keys for the POST method.
Here **query, payload & where** keys are required keys for PUT, PATCH, and DELETE methods.
**Note:** For POST, PUT, PATCH, and DELETE methods we can configure both INSERT as well as UPDATE queries.

## HTTP Request
### For HTTP GET request.
- http://localhost/Microservices/public_html/index.php?REQUEST_URI=/reload
- http://localhost/Microservices/public_html/index.php?REQUEST_URI=/routes

One can clean the URL by making the required changes in the web server .conf file.
### For HTTP POST, PUT, PATCH, and DELETE request.
- The JSON payload should be as below.
```
{ "data":
	{
		"key1": "value1",
		"key2": "value2",
		"key3": "value3",
		"key4": "value4",
	}
};
```
- For performing processing of multiple entries one can change to the payload as an array of entries.
```
{ "data": 
	[
		{
			"key1": "value1",
			"key2": "value2",
			"key3": "value3",
			"key4": "value4",
		},
		{
			"key1": "value1",
			"key2": "value2",
			"key3": "value3",
			"key4": "value4",
		}
	]
};
```
**Note:** For the PATCH method one can update a single field at a time.
```
{ "data":
	{
		"key1": "value1"
	}
};
```
- For performing the updation of multiple fields one can change the payload as an array of entries for the same {id}.

```
{ "data": 
	[
		{
			"key1": "value1"
		},
		{
			"key2": "value2",
		},
		{
			"key3": "value3",
		},
		{
			"key4": "value4",
		}
	]
};
```

