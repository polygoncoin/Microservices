<?php
define('HOST','127.0.0.1');
define('USER','root');
define('PASS','shames11');
define('PORT','3306');
define('DATABASE','sdk2');

require_once('project/redis.php');

if (!isset($_GET['crud']) {
    //404
}

$CONFIG = [
	'ADD'=>'create',
	'EDIT'=>'update',
	'LIST'=>'list',
	'DD_CONFIG'=>[  
	  	'analytics_column'=>[  
	     	'id'=>'value',
	     	'column_name'=>'label'
	  	],
	  	'bu'=>[  
	     	'id'=>'value',
	     	'name'=>'label'
	  	],
	  	'section'=>[  
	     	'id'=>'value',
	     	'name'=>'label'
	  	],
	  	'app'=>[  
	     	'id'=>'value',
	     	'name'=>'label'
	  	],
	  	'app_group'=>[  
	     	'id'=>'value',
	     	'name'=>'label'
	  	],
	  	'customer'=>[  
	     	'id'=>'value',
	     	'username'=>'label'
	  	],
	  	'page'=>[  
	     	'id'=>'value',
	     	'crud'=>'label'
	  	],
	  	'functionality'=>[  
	     	'id'=>'value',
	     	'name'=>'label'
	  	],
	  	'page_functionality'=>[  
	     	'id'=>'value',
	     	'name'=>'label'
	  	],
	  	'dashboard_section'=>[  
	     	'id'=>'value',
	     	'name'=>'label'
	  	],
	  	'group'=>[  
	     	'id'=>'value',
	     	'name'=>'label'
	  	],
	  	'country'=>[  
	     	'prefix'=>'value',
	     	'name'=>'label'
	  	],
	  	'event'=>[  
	     	'name'=>'label'
	  	],
	  	'event_param'=>[  
	     	'id'=>'value',
	     	'`desc`'=>'label'
	  	],
	  	'mobile_os'=>[  
	     	'name'=>'label'
	  	],
	  	'segment'=>[
	     	'id'=>'value',
	     	'name'=>'label'
	  	],
	  	'funnels'=>[  
	     	'id'=>'value',
	     	'funnel_name'=>'label'
	  	],
	  	'message'=>[  
	     	'id'=>'value',
	     	'message_title'=>'label'
	  	],
	  	'timezone'=>[  
	     	'CONCAT(id,\'_\',offset)'=>'value',
	     	'CONCAT(name,\' \',utc_dst)'=>'label'
	  	]
	],
	'DD_LEVEL'=>[
	  'crud_permission'=>1
	]
];

require_once('vendor/PDO/PDO.class.php');

$DB_SDK2 = new DB(HOST, PORT, DB_SDK2, USER, PASS);

if(!validateCRUD($params['crud'],$params['interface'])) {
	output404();
}