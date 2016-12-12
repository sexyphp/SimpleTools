<?php
// +----------------------------------------------------------------------
// | SimpleTools for PHP [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) http://www.sexyphp.com
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: lianger <breeze323136@126.com>
// +----------------------------------------------------------------------
/**
 * Class Packet
 * 微信现金红包入口
 *
 */
include_once 'CommonUtil.class.php';
class Packet{
	private $notify_url	=	'http://localhost/test/index';	//授权回调地址
	private $app_id 	= 	'';					//微信开放平台  appid
	private $app_secret = 	'';					//微信开放平台  appsecret
	private $app_mchid 	= 	'';					//微信商户平台 id
	private $key		= 	'';					//微信商户平台  api秘钥  md5('coder')
	protected $parameters; 	//cft 参数
	protected $commonUtil;	//工具类

	public function __construct(){

		$this->commonUtil	=	new CommonUtil();
		//do sth here....
	}


	/**
	 * 微信现金发放
	 * @param $mch_billno		商户订单号
	 * @param $openid			用户openid
	 * @param $total_amount		总金额，单位分
	 * @param string $wishing	红包祝福
	 * @param $nick_name		提供方名称
	 * @param $send_name		红包发送者名称
	 * @return SimpleXMLElement
	 */
	public function getprepay($mch_billno,$openid,$total_amount,$wishing='',$nick_name,$send_name)
	{

		$this->setParameter("nonce_str", 	$this->commonUtil->great_rand());//随机字符串，丌长于 32 位
		$this->setParameter("mch_billno",	$mch_billno );		//订单号		$this->app_mchid.date('YmdHis').rand(1000, 9999)
		$this->setParameter("mch_id", 		$this->app_mchid);	//商户号
		$this->setParameter("wxappid", 		$this->app_id);
		$this->setParameter("nick_name", 	$nick_name);		//提供方名称
		$this->setParameter("send_name", 	$send_name);		//红包发送者名称
		$this->setParameter("re_openid", 	$openid);			//接受红包的用户
		$this->setParameter("total_amount", $total_amount);		//付款金额，单位分
		$this->setParameter("min_value", 	100);				//最小红包金额，单位分
		$this->setParameter("max_value", 	2000);				//最大红包金额，单位分
		$this->setParameter("total_num", 	1);					//红包収放总人数
		$this->setParameter("wishing", 		$wishing);			//红包祝福诧
		$this->setParameter("client_ip", 	'127.0.0.1');		//调用接口的机器 Ip 地址
		$this->setParameter("act_name", 	'猜灯谜抢红包活动');			//活劢名称
		$this->setParameter("remark", 		'猜越多得越多，快来抢！！');			//备注信息
		$postXml = $this->create_hongbao_xml();
		$url = 'https://api.mch.weixin.qq.com/mmpaymkttransfers/sendredpack';
		$responseXml = $this->commonUtil->curl_post_ssl($url, $postXml);
		$responseObj = simplexml_load_string($responseXml, 'SimpleXMLElement', LIBXML_NOCDATA);
		return $responseObj;
	}



	/**
	 * 用户信息
	 * @param $param
	 * @return mixed
	 */
	public function userinfo(){

		if($_GET['param']=='access_token' && !empty($_GET['code'])){
			$json = $this->get_access_token($_GET['code']);
			if(!empty($json)){
				$userinfo = $this->get_user_info($json['access_token'],$json['openid']);
				return $userinfo;
			}
		}else{
			$this->get_authorize_url($this->notify_url.'?param=access_token','STATE');
		}

		return false;
	}



	/**
	 * 获取微信授权链接
	 *
	 * @param string $redirect_uri 跳转地址
	 * @param mixed $state 参数
	 */
	public function get_authorize_url($redirect_uri = '', $state = '')
	{
		$redirect_uri = urlencode($redirect_uri);
		$url = "https://open.weixin.qq.com/connect/oauth2/authorize?appid={$this->app_id}&redirect_uri={$redirect_uri}&response_type=code&scope=snsapi_userinfo&state={$state}#wechat_redirect";
		echo "<script language='javascript' type='text/javascript'>";
		echo "window.location.href='$url'";
		echo "</script>";
	}


	/**
	 * 获取授权token
	 * @param string $code	通过get_authorize_url获取到的code
	 * @return bool|mixed
	 */
	public function get_access_token($code = '')
	{
		$token_url = "https://api.weixin.qq.com/sns/oauth2/access_token?appid={$this->app_id}&secret={$this->app_secret}&code={$code}&grant_type=authorization_code";
		$token_data = $this->http($token_url);
		if(!empty($token_data[0]))
		{
			return json_decode($token_data[0], TRUE);
		}

		return FALSE;
	}


	/**
	 * 获取授权后的微信用户信息
	 * @param string $access_token
	 * @param string $open_id
	 * @return bool|mixed
	 */
	public function get_user_info($access_token = '', $open_id = '')
	{
		if($access_token && $open_id)
		{
			$access_url = "https://api.weixin.qq.com/sns/auth?access_token={$access_token}&openid={$open_id}";
			$access_data = $this->http($access_url);
			$access_info = json_decode($access_data[0], TRUE);
			if($access_info['errmsg']!='ok'){
				exit('页面过期');
			}
			$info_url = "https://api.weixin.qq.com/sns/userinfo?access_token={$access_token}&openid={$open_id}&lang=zh_CN";
			$info_data = $this->http($info_url);
			if(!empty($info_data[0]))
			{
				return json_decode($info_data[0], TRUE);
			}
		}

		return FALSE;
	}


	function setParameter($parameter, $parameterValue) {
		$this->parameters[CommonUtil::trimString($parameter)] = CommonUtil::trimString($parameterValue);
	}


	function getParameter($parameter) {
		return $this->parameters[$parameter];
	}


	function check_sign_parameters(){
		if($this->parameters["nonce_str"] == null ||
			$this->parameters["mch_billno"] == null ||
			$this->parameters["mch_id"] == null ||
			$this->parameters["wxappid"] == null ||
			$this->parameters["nick_name"] == null ||
			$this->parameters["send_name"] == null ||
			$this->parameters["re_openid"] == null ||
			$this->parameters["total_amount"] == null ||
			$this->parameters["max_value"] == null ||
			$this->parameters["total_num"] == null ||
			$this->parameters["wishing"] == null ||
			$this->parameters["client_ip"] == null ||
			$this->parameters["act_name"] == null ||
			$this->parameters["remark"] == null ||
			$this->parameters["min_value"] == null
		)
		{
			return false;
		}
		return true;

	}
	/**
	例如：
	appid：    wxd111665abv58f4f
	mch_id：    10000100
	device_info：  1000
	Body：    test
	nonce_str：  ibuaiVcKdpRxkhJA
	第一步：对参数按照 key=value 的格式，并按照参数名 ASCII 字典序排序如下：
	stringA="appid=wxd930ea5d5a258f4f&body=test&device_info=1000&mch_i
	d=10000100&nonce_str=ibuaiVcKdpRxkhJA";
	第二步：拼接支付密钥：
	stringSignTemp="stringA&key=192006250b4c09247ec02edce69f6a2d"
	sign=MD5(stringSignTemp).toUpperCase()="9A0A8659F005D6984697E2CA0A
	9CF3B7"
	 */
	protected function get_sign(){
		try {
			if (null == $this->key || "" == $this->key ) {
				throw new SDKRuntimeException("密钥不能为空！" . "<br>");
			}
			if($this->check_sign_parameters() == false) {   //检查生成签名参数
				throw new SDKRuntimeException("生成签名参数缺失！" . "<br>");
			}
			ksort($this->parameters);
			$unSignParaString = $this->commonUtil->formatQueryParaMap($this->parameters, false);


			return $this->commonUtil->sign($unSignParaString,$this->commonUtil->trimString($this->key));
		}catch (SDKRuntimeException $e)
		{
			die($e->errorMessage());
		}

	}

	//生成红包接口XML信息
	/*
	<xml>
		<sign>![CDATA[E1EE61A9]]</sign>
		<mch_billno>![CDATA[00100]]</mch_billno>
		<mch_id>![CDATA[888]]</mch_id>
		<wxappid>![CDATA[wxcbda96de0b165486]]</wxappid>
		<nick_name>![CDATA[nick_name]]</nick_name>
		<send_name>![CDATA[send_name]]</send_name>
		<re_openid>![CDATA[onqOjjXXXXXXXXX]]</re_openid>
		<total_amount>![CDATA[100]]</total_amount>
		<min_value>![CDATA[100]]</min_value>
		<max_value>![CDATA[100]]</max_value>
		<total_num>![CDATA[1]]</total_num>
		<wishing>![CDATA[恭喜发财]]</wishing>
		<client_ip>![CDATA[127.0.0.1]]</client_ip>
		<act_name>![CDATA[新年红包]]</act_name>
		<act_id>![CDATA[act_id]]</act_id>
		<remark>![CDATA[新年红包]]</remark>
	</xml>
	*/
	function create_hongbao_xml($retcode = 0, $reterrmsg = "ok"){
		try {
			$this->setParameter('sign', $this->get_sign());

			return  $this->commonUtil->arrayToXml($this->parameters);

		}catch (SDKRuntimeException $e)
		{
			die($e->errorMessage());
		}

	}

}
?>