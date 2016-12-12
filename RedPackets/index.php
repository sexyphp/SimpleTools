<?php 
//入口文件
include_once "Packet.class.php";
$packet = new Packet();

$userinfo	=	$packet->userinfo();

if(empty($userinfo['openid'])){
	exit('NOAUTH');
}else{

	$mch_billno		=	generateJnlNo();		//订单号
	$openid			=	$userinfo['openid'];	//接受红包的用户
	$total_amount	=	'100';					//付款金额，单位分
	$wishing		=	'恭喜发财';				//红包祝福
	$nick_name		=	'提供方名称';				//提供方名称
	$send_name		=	'红包发送者名称';			//红包发送者名称
	$demo = $packet->getprepay($mch_billno,$openid,$total_amount,$wishing,$nick_name,$send_name);
	debug($demo);
}




/**
 * Text:	生成唯一订单号
 * Desc:	根据时间算法生成唯一订单号
 * @return string
 * User:   lianger  <sexyphp.com>
 * CrDt:	2016-06-08
 * UpDt:
 */
function generateJnlNo() {
	date_default_timezone_set('PRC');
	$yCode    = array('A','B','C','D','E','F','G','H','I','J');
	$orderSn  = '';
	$orderSn .= $yCode[(intval(date('Y')) - 1970) % 10];
	$orderSn .= strtoupper(dechex(date('m')));
	$orderSn .= date('d').substr(time(), -5);
	$orderSn .= substr(microtime(), 2, 5);
	$orderSn .= sprintf('%02d', mt_rand(0, 99));
	//echo $orderSn,PHP_EOL;     //得到唯一订单号：G107347128750079
	return $orderSn;
}


/**
 * Text:		debug调试工具
 * Desc:		断点调试窗口
 * @param 		$data
 * User:    	lianger  <sexyphp.com>
 * CrDt:		2016-03-20
 * UpDt:
 */
function debug($data){

	if(empty($data)){
		echo "<pre style='background-color: #000;color: #fff;font-size: 14px;height: 100px;line-height: 50px;'>";
		echo "<span style='margin-left: 20px;font-size: 18px;'>";
		var_dump($data);
		echo "</span>";
		echo "</pre>";
		die;
	}

	if(!is_array($data)){
		echo "<pre style='background-color: #000;color: #fff;font-size: 14px;min-height: 100px;line-height: 50px;'>";
		echo "<span style='margin-left: 20px;font-size: 18px;'>";
		print_r($data);
		echo "</span>";
		echo "</pre>";
		die;
	}


	echo "<pre style='background-color: #000;color: #fff;font-size: 14px;min-height: 100px;'>";
	echo "<br /><br /><br /><span style='margin-left: 20px;font-size: 13px;'>";
	print_r($data);
	echo "</span><br /><br /><br />";
	echo "</pre>";
	die;
}