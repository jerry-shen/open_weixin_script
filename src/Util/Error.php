<?php
/**
 * @todo  报错信息类
 * @author huanghailong <hailong.huang@eub-inc.com>
 * @version 2017-04-19
 * @internal 输出错误信息
 */
namespace Util;

class Error {

	const CODE_LIST = [
		// Common/Mp
		'40001' => '接口获取数据为空！',
		'40002' => '数据格式错误！',
		'40003' => '接口错误！',
		// Common/Driver
		'50001' => '数据不完整！',
		// Common/Mp/Dim
		'60001' => 'qrcode 接口数据格式错误！',
	];

	static function msg ($code, $data = null) {

		return ['err_code'=>$code, 'err_msg'=>self::CODE_LIST[$code], 'err_data'=>$data];

	}

}
