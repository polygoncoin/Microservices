
# Microservices!

Hi! This is the first very light and easy **Microservices Framework** package which can be configured very easily and your API's can be up in a few minutes.

## Folders

-  **/App** Basic Microservices application folder

-  **/Config** Basic Microservices configuration folder

-  **/public_html** Microservices doc root folder

-  **/ThirdParty** Folder containing Classes to perform HTTP request via cURL to the Third Parties

-  **/Crons** Crons classes folder

-  **/Dropbox** Folder for uploaded files.

## Files

-  **/.env.example** Create a copy of this file as **.env**

-  **/global.sql** Import this SQL file on your **MySQL** instance

-  **/public_html/index.php** This is the file to be accessed for the API's

## .env

- One needs to set the configurations in the **.env** file. The configurations include Cache/Database creds and other credentials.

- Below are the default server configuration parameters.

```
dbHostnameDefault='127.0.0.1'
dbUsernameDefault='root'
dbPasswordDefault='shames11'
```

- Database details on default MySQL server

```
globalDbName='global'
clientMasterDbName='client_master'
```

- If there is a requirement from your client for a **Separate DB or Host** for saving his data, just set these settings here and configure these **.env variables in the global.m004_master_connection table**

- For a different DB on default Host

```
dbDatabaseClient001='client_001'
```

- For a different Host/DB instance client 1

```
dbHostnameClient001='127.0.0.1'
dbUsernameClient001='root'
dbPasswordClient001='shames11'
dbDatabaseClient001='client_001'
```

- For a different Host/DB instance client 2

```
dbHostnameClient002='127.0.0.1'
dbUsernameClient002='root'
dbPasswordClient002='shames11'
dbDatabaseClient002='client_002'
```

- This can extend to any number of databases on the default host or to any number of dedicated hosts for respective clients.

**Note:**

- The connection configuration used for an API is limited to the respective group. One group access is limited to one connection configuration.

- All the configuration here is with respect to the group. Users, Routes & Connections are configured and grouped inside a **GROUP**

- Any change made to the global database needs to be updated in the cache server. This can be done by accessing **/reload** route. This is restricted by HTTP Authentication and configuration for same can be found in .env as below.

```
HttpAuthenticationRestrictedIp='127.0.0.1'
HttpAuthenticationUser='username'
HttpAuthenticationPassword='password'
```

## Configuring route

### Files

-  **/Config/Routes/GETroutes.php** for all GET method routes configuration.

-  **/Config/Routes/POSTroutes.php** for all POST method routes configuration.

-  **/Config/Routes/PUTroutes.php** for all PUT method routes configuration.

-  **/Config/Routes/PATCHroutes.php** for all PATCH method routes configuration.

-  **/Config/Routes/DELETEroutes.php** for all DELETE method routes configuration.

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
		],
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

-  **/global/{table:string}** where **table** is the dynamic string

```
INSERT INTO `m003_master_route`(`route`) VALUES ('/global/{table:string}');
```
After inserting one needs to configure this route for use.

To configure for GET, and POST methods.

```
INSERT INTO
	`l001_link_allowed_route`
SET
	`group_id` = 1,
	`route_id` = 1, -- Insert id of route query
	`http_id` = 1 -- 1 is for GET method
;
```
```
INSERT INTO
	`l001_link_allowed_route`
SET
	`group_id` = 1,
	`route_id` = 1, -- Insert id of route query
	`http_id` = 2 -- 2 is for POST method
;
```

So route /global/{table:string} can be used for adding and fetching table records

-  **/global/{table:string}/{id:int}** where **table** is the dynamic string and **id** is a dynamic integer
```
INSERT INTO `m003_master_route`(`route`) VALUES ('/global/{table:string}/{id:int}');
```
After inserting one needs to configure this route for use.

To configure for GET/PUT/PATCH/DELETE method.
```
INSERT INTO
	`l001_link_allowed_route`
SET
	`group_id` = 1,
	`route_id` = 2, -- Insert id of route query.
	`http_id` = 1 -- 1 is for GET method
;
```
```
INSERT INTO
	`l001_link_allowed_route`
SET
	`group_id` = 1,
	`route_id` = 2, -- Insert id of route query.
	`http_id` = 3 -- 3 is for PUT method
;
```
```
INSERT INTO
	`l001_link_allowed_route`
SET
	`group_id` = 1,
	`route_id` = 2, -- Insert id of route query.
	`http_id` = 4 -- 4 is for PATCH method
;
```
```
INSERT INTO
	`l001_link_allowed_route`
SET
	`group_id` = 1,
	`route_id` = 2, -- Insert id of route query.
	`http_id` = 5 -- 5 is for DELETE method
;
```
So route /global/{table:string}/{id:int} can be used for updating and fetching a specific record of a table.

## Configuring SQL's

### Folder

-  **/Config/Queries/GlobalDB** for global database.

-  **/Config/Queries/ClientDB** for all client including all databases & hosts.

### Files - GlobalDB

-  **/Config/Queries/GlobalDB/GET/<filename>.php** GET method SQL.

-  **/Config/Queries/GlobalDB/POST/<filename>.php** POST method SQL.

-  **/Config/Queries/GlobalDB/PUT/<filename>.php** PUT method SQL.

-  **/Config/Queries/GlobalDB/PATCH/<filename>.php** PATCH method SQL.

-  **/Config/Queries/GlobalDB/DELETE/<filename>.php** DELETE method SQL.

### Files - ClientDB

-  **/Config/Queries/ClientDB/GET/<filename>.php** GET method SQL.

-  **/Config/Queries/ClientDB/POST/<filename>.php** POST method SQL.

-  **/Config/Queries/ClientDB/PUT/<filename>.php** PUT method SQL.

-  **/Config/Queries/ClientDB/PATCH/<filename>.php** PATCH method SQL.

-  **/Config/Queries/ClientDB/DELETE/<filename>.php** DELETE method SQL.

Where **<filename>.php** are different file names for respective functionality.

### SQL's

The supported SQL format are as below

- For GET method.

```
<?php
return [
	'query' => "SELECT * FROM {$this->globalDB}.TableName WHERE id = ? AND group_id = ? AND client_id = ?",
	'where' => [
		//column => [uriParams|payload|readOnlySession|{custom}, key|{value}],
		'id' => ['uriParams', 'id'],
		'group_id' => ['payload', 'group_id'],
		'client_id' => ['readOnlySession', 'client_id']
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
			'fnArgs' => [
				//variable => [uriParams|payload|readOnlySession|insertIdParams|{custom}, key|{value}],
				'group_id' => ['payload', 'group_id']
			],
			'errorMessage' => 'Invalid Group Id'
		],
		[
			'fn' => 'validateClientId',
			'fnArgs' => [
				//variable => [uriParams|payload|readOnlySession|insertIdParams|{custom}, key|{value}],
				'client_id' => ['payload', 'client_id']
			],
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
		//column => [uriParams|payload|readOnlySession|insertIdParams|{custom}, key|{value}],
		'group_id' => ['payload', 'group_id'],
		'client_id' => ['readOnlySession', 'client_id']
	],
	'where' => [// for __WHERE__
	//column => [uriParams|payload|readOnlySession|{custom}, key|{value}],
		'id' => ['uriParams', 'id']
	],
	'insertId' => 'm001_master_group:id',// Last insert id key name in $input['insertIdParams'][key name];
	'subQuery' => [
		[
			'query' => "MySQL Query here",
			'payload' => [
				'previous_table_id' => ['insertIdParams', 'm001_master_group:id'],
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
			'fnArgs' => [
				//variable => [uriParams|payload|readOnlySession|insertIdParams|{custom}, key|{value}],
				'group_id' => ['payload', 'group_id']
			],
			'errorMessage' => 'Invalid Group Id'
		],
	]
];
```
Here **query & payload** keys are required keys for the POST method.
For PUT, PATCH, and DELETE methods **query, payload & where** keys are required keys.
**Note:** For POST, PUT, PATCH, and DELETE methods we can configure both INSERT as well as UPDATE queries. **Also for these methods usage of \_\_SET__ and \_\_WHERE__ is necessary**
Example Queries can be like
```
INSERT INTO {$this->globalDB}.TableName SET __SET__;
```
```
UPDATE {$this->globalDB}.TableName SET __SET__ WHERE __WHERE__;
```
## HTTP Request

### For HTTP GET request.

- http://localhost/Microservices/public_html/index.php?REQUEST_URI=/reload

- http://localhost/Microservices/public_html/index.php?REQUEST_URI=/global/tableName/1

One can clean the URL by making the required changes in the web server .conf file.

### For HTTP POST, PUT, PATCH, and DELETE requests.

- The JSON payload should be as below.

```
{"data":
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
{"data":
	[
		{
			"key1": "value1",
			"key2": "value2",
			"key3": "value3",
			"key4": "value4",
		},
		{
			"key1": "value21",
			"key2": "value22",
			"key3": "value23",
			"key4": "value24",
		}
	]
};
```
**Note:** For the PATCH method one can update a single field at a time.

```
{"data":
	{"key1": "value1"}
};
```
- For performing the updation of multiple fields one can change the payload as an array of entries for the same {id}.

```
{"data":
	[
		{"key1": "value1"},
		{"key2": "value2"},
		{"key3": "value3"},
		{"key4": "value4"}
	]
};
```

## Variables

### $input

$input variable has following keys available.

#### uriParams

**$input['uriParams']** contains the dynamically resolved values.
Suppose our configured route is **/global/{table:string}/{id:int}** and we make an HTTP request for **/global/tableName/1** then $input['uriParams'] will hold these dynamic values as below.

```
$input['uriParams'] = [
	'table' => 'tableName'
	'id' => 1
];
```

#### readOnlySession

**$input['readOnlySession']** contains the session related values for respective users.

This is not dependent on route accessed. This remains same for every request and is dependent on **Authorization Bearer Token**.

To list a sample of available data one can access.

```
$input['readOnlySession'] = {
	"id": 1,
	"username": "shames11@rediffmail.com",
	"password_hash": "$2y$10$o8hFTjBIXQS.fOED2Ut1ZOCSdDjTnS3lyELI4rWyFEnu4GUyJr3O6",
	"group_id": 1,
	"client_id": 1
};
```
This is made available through Cache server i.e. **Redis**.
If you want to add more fields to be made available just change the logic in **Reload.php** and the tables in **global database**.

#### payload

**$input['payload']** contains the request data.

For **GET** method, the **$_GET** is made available.

For **POST/PUT/PATCH/DELETE** as discussed above we send request as 

```
{"data":
	{
		"key1": "value1",
		"key2": "value2",
		"key3": "value3",
		"key4": "value4",
	}
};
```
This will make **$input['payload']** data available as for each iteration.

```
$input['payload'] = {
	"key1": "value1",
	"key2": "value2",
	"key3": "value3",
	"key4": "value4",
};
```

**Note** the **data** key disappears.

#### insertIdParams

**$input['insertIdParams']** contains the insert ids with respective keys configured.

For **POST/PUT/PATCH/DELETE** as discussed we can perform both INSERT as well as UPDATE operation. The  insertIdParams contains the insert ids of the executed INSERT queries. 

As we have seen a configuration

```
'insertId' => 'm001_master_group:id'
```
This means the insertId needs to be collected as key **m001_master_group:id**.

This will make **$input['insertIdParams']** data available as below for each iteration.

```
$input['insertIdParams'] = {
	"m001_master_group:id": 123
};
```

The variable $input['insertIdParams'] will append all the values with respective keys which you want to use and are configured.

#### custom

For any HTTP requests we want to use a custom value. For example a where clause or setting a payload

```
	'where' => [// for __WHERE__
		'id' => ['uriParams', 'id'],
		'updated_by' => ['readOnlySession', 'id'],
		'is_approved' => ['custom', 'Yes']
	],
```
Here is_approved will change to Yes in the database.

Similarly, we can use this in payload as well to set a static values instead of dynamic values from layload.

```
'payload' => [
	'client_id' => ['insertIdParams', 'm001_master_group:id'],
	'approved_by' => ['readOnlySession', 'id'],
	'updated_date' => ['custom', date('Y-m-d')]
],
```
