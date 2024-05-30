# Microservices!
This very light and easy **Microservices Framework** package can be configured very easily to create your API's in a very short time if you are done with your database creation.

## Files
-  **/.env.example** Create a copy of this file as **.env**
-  **/global.sql** Import this SQL file on your **MySQL global** instance
-  **/client_master.sql** Import this SQL file on your **MySQL client** instance
-  **/cache.sql** Import this SQL file for managing cache (e.g, token details etc.) in **MySQL** instance instead of Redis (To be configured in .env)
 
## .env.example
- One needs to make a copy of this file as .env and make changes in this newly created file. This file will contain configurations pertaining to your APIs using Cache and DB servers.
- Below are the default databse server configuration.
```
defaultDbHostname='127.0.0.1'
defaultDbUsername='root'
defaultDbPassword='shames11'
```
- Database details on default MySQL server
```
defaultDbDatabase='global'
clientMasterDbName='client_master'
```
Note clientMasterDbName can be set same as defaultDbDatabase if you dont want to maintain different databases for client.

- For a **Dedicated DB or Host** set these settings as below and update them in **global.m003_master_connection** table
```
// Same Server but different DB.
dbDatabaseClient001='client_001'
```
```
// Dedicated DB Server.
dbHostnameClient001='127.0.0.1'
dbUsernameClient001='root'
dbPasswordClient001='shames11'
dbDatabaseClient001='client_001'
```
- Any change made to the global database needs to be updated in the cache server. This can be done by accessing **/reload** route. This is restricted by HTTP Authentication and configuration for same can be found in .env as below.

```
HttpAuthenticationRestrictedIp='127.0.0.1'
HttpAuthenticationUser='username'
HttpAuthenticationPassword='password'
```

- The connection configuration used for the API is limited to the respective group.

## Folders
-  **/App** Basic Microservices application folder
-  **/Config** Basic Microservices configuration folder
-  **/public_html** Microservices doc root folder
-  **/ThirdParty** Folder containing Classes to perform HTTP request via cURL to the Third Parties
-  **/Crons** Crons classes folder
-  **/Dropbox** Folder for uploaded files.
-  
## Configuring route

### Files
-  **/Config/Routes/&lt;GroupName&gt;/GETroutes.php** for all GET method routes configuration.
-  **/Config/Routes/&lt;GroupName&gt;/POSTroutes.php** for all POST method routes configuration.
-  **/Config/Routes/&lt;GroupName&gt;/PUTroutes.php** for all PUT method routes configuration.
-  **/Config/Routes/&lt;GroupName&gt;/PATCHroutes.php** for all PATCH method routes configuration.
-  **/Config/Routes/&lt;GroupName&gt;/DELETEroutes.php** for all DELETE method routes configuration.
**&lt;GroupName&gt;** These are corresponding to the assigned group to a user for accessing the API's
- For configuring route **/tableName/parts** GET method
```
return [
    'tableName' => [
        'parts' => [
            '__file__' => 'SQL file location'
        ]
    ]
];
```
- For configuring route **/tableName/{id}** where id is dynamic **integer** value to be collected.
```
return [
    'tableName' => [
        '{id:int}' => [
            '__file__' => 'SQL file location'
        ]
    ]
];
```
- Same dynamic variable but with a different data type, for e.g. **{id}** will be treated differently for **string** and **integer** values to be collected.
```
return [
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
return [
    '{tableName:string|admin,group,client,routes}' => [
        '{id:int}' => [
            '__file__' => 'SQL file location'
        ]
    ]
];
```

## Configuring SQL's
### Folder
-  **/Config/Queries/GlobalDB** for global database.
-  **/Config/Queries/ClientDB** for Clients (including all hosts and their databases).

### Files - GlobalDB
-  **/Config/Queries/GlobalDB/GET/&lt;filename&gt;.php** GET method SQL.
-  **/Config/Queries/GlobalDB/POST/&lt;filename&gt;.php** POST method SQL.
-  **/Config/Queries/GlobalDB/PUT/&lt;filename&gt;.php** PUT method SQL.
-  **/Config/Queries/GlobalDB/PATCH/&lt;filename&gt;.php** PATCH method SQL.
-  **/Config/Queries/GlobalDB/DELETE/&lt;filename&gt;.php** DELETE method SQL.

### Files - ClientDB
-  **/Config/Queries/ClientDB/GET/&lt;filename&gt;.php** GET method SQL.
-  **/Config/Queries/ClientDB/POST/&lt;filename&gt;.php** POST method SQL.
-  **/Config/Queries/ClientDB/PUT/&lt;filename&gt;.php** PUT method SQL.
-  **/Config/Queries/ClientDB/PATCH/&lt;filename&gt;.php** PATCH method SQL.
-  **/Config/Queries/ClientDB/DELETE/&lt;filename&gt;.php** DELETE method SQL.
One can replace **&lt;filename&gt;** tag with desired name as per functionality.

### SQL's

The supported SQL format are as below

- For GET method.
```
<?php
return [
    'query' => "SELECT * FROM {$Env::$globalDB}.TableName WHERE id = ? AND group_id = ? AND client_id = ?",
    '__WHERE__' => [
        //column => [uriParams|payload|function|readOnlySession|{custom}, key|{value}]
        'id' => ['uriParams', 'id'],
        'group_id' => ['payload', 'group_id'],
        'client_id' => ['readOnlySession', 'client_id']
    ],
    'mode' => 'singleRowFormat',//Single row returned.
    'subQuery' => [
        'Clients' => [
            'query' => "MySQL Query here",
            '__WHERE__' => [],
            'mode' => 'multipleRowFormat'//Multiple rows returned.
        ],
        'Users' => [
            'query' => "MySQL Query here",
            '__WHERE__' => [],
            'mode' => 'multipleRowFormat'//Multiple rows returned.
        ],
        'modules' => ...
    ],
    'validate' => [
        [
            'fn' => 'validateGroupId',
            'fnArgs' => [
                'group_id' => ['payload', 'group_id']
            ],
            'errorMessage' => 'Invalid Group Id'
        ],
        [
            'fn' => 'validateClientId',
            'fnArgs' => [
                'client_id' => ['payload', 'client_id']
            ],
            'errorMessage' => 'Invalid Client Id'
        ],
    ]
];
```
Here **query & mode** keys are required keys
**Note:** For GET method **payload** is query string parameters;basically **$_GET**.

- For POST/PUT/PATCH/DELETE method.

```
<?php
return [
    'query' => "INSERT {$Env::$globalDB}.TableName SET __SET__ WHERE __WHERE__ ",
    // Only fields present in __CONFIG__ shall be supported. Both Required and Optional
    '__CONFIG__' => [// Set your payload fields config here.
        // [{payload/uriParams}, key/index, {Constants::$REQUIRED}]
        ['payload', 'group_id', Constants::$REQUIRED], // Required field
        ['payload', 'group_id'], // Optional field
    ],
    '__SET__' => [// for __SET__
        //column => [uriParams|payload|function|readOnlySession|insertIdParams|{custom}, key|{value}],
        'group_id' => ['payload', 'group_id'],
        'client_id' => ['readOnlySession', 'client_id']
    ],
    '__WHERE__' => [// for __WHERE__
        //column => [uriParams|payload|function|readOnlySession|insertIdParams|{custom}, key|{value},         'id' => ['uriParams', 'id']
    ],
    'insertId' => 'tablename1:id',// Last insert id key name in $input['insertIdParams'][<tableName>:id];
    'subQuery' => [
        'module1' => [
            'query' => "MySQL Query here",
            '__SET__' => [
                'previous_table_id' => ['insertIdParams', '<tableName>:id'],
            ],
            '__WHERE__' => [],
        ],
        'module2' => ...
    ],
    'validate' => [
        [
            'fn' => 'validateGroupId',
            'fnArgs' => [
                'group_id' => ['payload', 'group_id']
            ],
            'errorMessage' => 'Invalid Group Id'
        ],
    ]
];
```
Note: If there are few modules or query configurations repeated or reused; one can palce them in a seperate file and include them as below.
```
// reusefilename.php
return [
    'query' => "MySQL Query here",
    '__SET__' => [],
    '__WHERE__' => [],
    'validate' => [
        [
            'fn' => 'validateModule3Id',
            'fnArgs' => [
                'module_id' => ['payload', 'module_id']
            ],
            'errorMessage' => 'Invalid module id'
        ],
    ]
],

```
The reuse version is as below.
```
    'subQuery' => [
        //Here the module1 properties are reused for write operation.
        'module1' => include DOC_ROOT . '/Config/Queries/ClientDB/Common/reusefilename.php',
    ]
```
Here **query & payload** keys are required keys for the POST method.
For PUT, PATCH, and DELETE methods **query, payload & where** keys are required keys.
**Note:** For POST, PUT, PATCH, and DELETE methods we can configure both INSERT as well as UPDATE queries. **Also for these methods usage of \_\_SET__ and \_\_WHERE__ is necessary**
Example Queries can be like
```
INSERT INTO {$Env::$globalDB}.TableName SET __SET__;
```
```
UPDATE {$Env::$globalDB}.TableName SET __SET__ WHERE __WHERE__;
```
## HTTP Request
### For HTTP GET request.
- http://localhost/Microservices/public_html/index.php?r=/reload
- http://localhost/Microservices/public_html/index.php?r=/tableName/1
One can clean the URL by making the required changes in the web server .conf file.

### For HTTP POST, PUT, PATCH, and DELETE requests.
- The JSON payload should be as below.
```
{"Payload":
    {
        "key1": "value1",
        "key2": "value2",
        ...
    }
};
```
- For performing processing of multiple entries one can change to the payload as an array of entries.
```
{"Payload":
    [
        {
            "key1": "value1",
            "key2": "value2",
            ...
        },
        {
            "key1": "value21",
            "key2": "value22",
            ...
        }
        ...
    ]
};
```
**Note:** For the PATCH method one can update a single field at a time.
```
{"Payload":
    {"key1": "value1"}
};
```
- For performing the updation of multiple fields one can change the payload as an array of entries for the same {id}.
```
{"Payload":
    [
        {"key1": "value1"},
        {"key2": "value2"},
        ...
    ]
};
```
## Variables

### $input

- **$input['uriParams']** Data passed in URI.
Suppose our configured route is **/{table:string}/{id:int}** and we make an HTTP request for **/tableName/1** then $input['uriParams'] will hold these dynamic values as below.
- **$input['readOnlySession']** Session Data.
This remains same for every request and contains keys like id, group_id, client_id
- **$input['payload']** Request data.
For **GET** method, the **$_GET** is the payload.
- **$input['insertIdParams']** Insert ids Data as per configuration.
For **POST/PUT/PATCH/DELETE** we perform both INSERT as well as UPDATE operation. The  insertIdParams contains the insert ids of the executed INSERT queries.

Other than these, one can use keyword **custom**, **functions** as below.

```
'__SET__' => [
    'client_id' => ['insertIdParams', 'm001_master_group:id'],
    'password' => ['function', function() {
        return password_hash(HttpRequest::$input['payload']['password'], PASSWORD_DEFAULT);
    }],
    'approved_by' => ['readOnlySession', 'id'],
    'updated_date' => ['custom', date('Y-m-d')]
],
```

## useHierarchy in /Config/Queries/*

### GET - READ

- Microservices/Config/Queries/ClientDB/GET/Category.php
In this file one can confirm how previous select data is used recursively in subQuery select as indicated by useHierarchy flag.
```
                'parent_id' => ['hierarchyData', 'root:id'],
```

### POST/PUT/PATCH/DELETE - Write

- Microservices/Config/Queries/ClientDB/POST/Category.php
Here one request can handle one to many hierarchy to any number of levels as per configuration.
Sample data payload below

```
{"Payload":
    {
       "name":"wewe",
       "sub":[
           {
              "subname":"email1",
              "subsub":[
                 {"subsubname":"address11"},
                 {"subsubname":"address12"},
                 ...
              ]
           },
           {
              "subname":"email2",
              "subsub":[
                 {"subsubname":"address21"},
                 {"subsubname":"address21"},
                 ...
              ]
           },
           ...
        ]
    }
}
```

## r=&lt;/route&gt;/config

### /tableName/{id}/config

- Adding keyword **config** at the end of route after a slash returns the payload information that should be supplied; both required and optional with desired format.

### Examples:

- r=/registration/config
- r=/category/config

One need to configure for same in route with a flag as **config => true**
Only these configured routes will be supported the config feature.

- For controlling globally there is a flag in env file labled **allowConfigRequest**

### r=/routes

This lists down all allowed routes for HTTP methods respectively.
