<?php

// 包含环境相关的配置项
require dirname(__FILE__) . "/config.php";

// This is the main Web application configuration. Any writable
// CWebApplication properties can be configured here.
return array(
	'basePath'=>dirname(__FILE__).DIRECTORY_SEPARATOR.'..',
	'name'=>'Poppy',

	'language'	=>	'zh_cn',


	// autoloading model and component classes
	'import'=>array(
		'application.models.*',
		'application.components.*',
	),

	// application components
	'components'=>array(

		// uncomment the following to set up database
		'db'=>array(
			'connectionString'=>DB_CON_STRING,
			'username'=>DB_USER_STRING,
			'password'=>DB_PW_STRING,
			'charset' =>'utf8',
		),
	),

);
