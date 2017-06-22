<?php
    if (!$loader = include __DIR__ . '/../vendor/autoload.php') {
        die('You must set up the project dependencies.');
    }
    date_default_timezone_set("PRC");
	ini_set('date.timezone', 'Asia/Shanghai');
	define('BI_SCRIPT_ENV', 'product');

	$globalConfig = include __DIR__ . '/../conf/config.php';
