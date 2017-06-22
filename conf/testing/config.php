<?php 
$config = [
	'db' => [
		'database_type' => 'mysql',
	    'database_name' => '',
	    'server' => '',
	    'username' => '',
	    'password' => '',
	    'charset' => 'utf8'
	],
    'wx' => [
        'app_id'  => '',
        'token'   => '',
        'aes_key' => ''
    ],
    'redis' => [
        'host' => '127.0.0.1',
        'port' => '6379',
        'auth' => '',
        'index' => 1,
        'message_queue' => 'mp_push_queue',
    ],
];
