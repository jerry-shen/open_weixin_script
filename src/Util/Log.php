<?php 
namespace Util;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

class Log {

	static function error ($app_name, $script_name, $channel_name, $data) {

		$file_path = __DIR__ . '/../../logs/' . $app_name . '/' . date('Y-m-d', time()) .'/' .$script_name. '.log';

		$log = new Logger($channel_name);
		$log->pushHandler(new StreamHandler($file_path));
		$log->error(json_encode($data));

	}

}
