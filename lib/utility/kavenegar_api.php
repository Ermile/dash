<?php
namespace dash\utility;
/*
@ Kavenegar Api
@ Version: 2.1
@ Author: Javad Evazzadeh | Evazzadeh.com

Quick Start:
	copy this file in your project and edit kavenegar_api.php first line and insert your apikey and linenumber
	then copy and paste below line to quickly sent message!
		require("kavenegar_api.php");
		$api 	= new kavenegar_api();
		$result = $api->send('09120000000', 'Hi, This is for test!');


How to Use:
	for use this class you must require this file in your project with below line
		require("kavenegar_api.php");

	then you must create an instance from kavenegar_api with below line
		$api = new kavenegar_api();

	you can set the apikey and linenumber from declaration part of class in first lines
	but if you want you can set this parameter on create new instance with below code
		$api = new kavenegar_api('Your-apikey', 'Your-linenumber');

	for use the functions you can use below line sample, this line send message to 09120000000
		$result = $api->send('09120000000', 'Hi, This is for test!');

	if you want you can set the line number value after initializing class
		$api->linenumber = '100020003000';

	for access current status and server message you can read status value with below line
	var_dump($api->status);
	var_dump($api->msg);

*/
class kavenegar_api
{
	// declare variable
	// you can replace null with your api code or your default linenumber
	protected $apikey  = '__YOUR API KEY__';
	public $linenumber = '__YOUR LINE NUMBER__';
	public $status     = null;
	public $msg        = null;
	const apipath      = "https://api.kavenegar.com/v1/%s/%s/%s.json";

	/**
	 * set api key and line number
	 *
	 * @param      <type>  $_apikey      The apikey
	 * @param      <type>  $_linenumber  The linenumber
	 */
	public function __construct($_apikey = null, $_linenumber = null)
	{
		$this->apikey     = (is_null($_apikey))? $this->apikey: $_apikey;
		$this->linenumber = (is_null($_linenumber))? $this->linenumber: $_linenumber;
	}


	/**
	 * Gets the path.
	 *
	 * @param      <type>  $_method  The method
	 * @param      string  $_base    The base
	 *
	 * @return     <type>  The path.
	 */
	private function get_path($_method, $_base = 'sms')
	{
		return sprintf(self::apipath, $this->apikey, $_base,$_method);
	}


	/**
	 * curl to kavenagar
	 *
	 * @param      <type>   $_url   The url
	 * @param      <type>   $_data  The data
	 *
	 * @return     integer  ( description_of_the_return_value )
	 */
	private function execute($_url, $_data, $_long_time_out = false)
	{
		$headers = array (
			'Accept: application/json',
			'Content-Type: application/x-www-form-urlencoded',
			'charset: utf-8'
		);
		$fields_string = null;
		if(!is_null($_data))
		{
			foreach($_data as $key => $value)
			{
				$fields_string.= $key.'='.$value.'&';
			}
			rtrim($fields_string, '&');
		}
		// for debug you can uncomment below line to see the send parameters
		if($_long_time_out)
		{
			$CONNECTTIMEOUT = 30;
			$TIMEOUT        = 40;

		}
		else
		{
			$CONNECTTIMEOUT = 3;
			$TIMEOUT        = 2;
		}

		//======================================================================================//
		if(function_exists('curl_init'))
		{
			$handle   = curl_init();
			curl_setopt($handle, CURLOPT_URL, $_url);
			curl_setopt($handle, CURLOPT_HTTPHEADER, $headers);
			curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($handle, CURLOPT_SSL_VERIFYHOST, false);
			curl_setopt($handle, CURLOPT_SSL_VERIFYPEER, false);
			curl_setopt($handle, CURLOPT_POST, true);
			curl_setopt($handle, CURLOPT_POSTFIELDS, $fields_string);
			// add timer to ajax request
			curl_setopt($handle, CURLOPT_CONNECTTIMEOUT, $CONNECTTIMEOUT);
			curl_setopt($handle, CURLOPT_TIMEOUT, $TIMEOUT);

			$response = curl_exec($handle);
			$mycode   = curl_getinfo($handle, CURLINFO_HTTP_CODE);
			// check mycode in special situation, if has default code with status handle it
			curl_close ($handle);
			//=====================================================================================//
			// for debug you can uncomment below line to see the result get from server

			if(!$response)
			{
				$this->status = -1;
				$this->msg    = null;
				return false;
			}

			$json_data		= json_decode($response, true);

			if(isset($json_data["return"]["status"]))
			{
				$this->status = $json_data["return"]["status"];
			}

			if(isset($json_data["return"]["message"]))
			{
				$this->msg = $json_data["return"]["message"];
			}

			return $json_data;
		}
		else
		{
			return null;
		}
	}


	/**
	 * send sms
	 *
	 * @return     <type>  ( description_of_the_return_value )
	 */
	public function send($_mobile, $_msg, $_type = 1, $_date = 0, $_localid = null)
	{
		$path 		= $this->get_path(__FUNCTION__);

		$params 	=
		[
			"receptor"       => $_mobile,
			"sender"         => $this->linenumber,
			"message"        => $_msg,
			"type"           => $_type,
			// "date"           => $_date,
			"localid" => $_localid,
		];

		if($_date)
		{
			$params['date'] = $_date;
		}

		$json = $this->execute($path, $params);

		if(!is_array($json))
		{
			return $this->status;
		}

		if(isset($json[0]))
		{
			return $json[0];
		}
		else
		{
			return false;
		}
	}


	/**
	 * send array
	 *
	 * @param      <type>   $_sender    The sender
	 * @param      <type>   $_receptor  The receptor
	 * @param      <type>   $_message   The message
	 * @param      integer  $_type      The type
	 * @param      integer  $_date      The date
	 *
	 * @return     <type>   ( description_of_the_return_value )
	 */
	public function sendarray($_sender, $_receptor, $_message, $_type= 1, $_date= 0)
	{
		$sender         = [];
		$message        = [];
		$type           = [];
		$receptor_count = count($_receptor);

		if(is_array($_sender))
		{
			$sender = $_sender;
		}
		else
		{
			for ($i = 0; $i < $receptor_count; $i++)
			{
				array_push($sender, $_sender);
			}
		}

		if(is_array($_message))
		{
			$message = $_message;
		}
		else
		{
			for ($i = 0; $i < $receptor_count; $i++)
			{
				array_push($message, $_message);
			}
		}

		if(is_array($_type))
		{
			$type 	= $_type;
		}
		else
		{
			for ($i = 0; $i < $receptor_count; $i++)
			{
				array_push($type, $_type);
			}
		}

		$path 		= $this->get_path(__FUNCTION__);
		$params 	=
		[
			"receptor" => json_encode($_receptor),
			"sender"   => json_encode($sender),
			"message"  => json_encode($message),
			"type"     => json_encode($type),
			"date"     => $_date,
		];

		$json = $this->execute($path, $params);

		if(!is_array($json))
		{
			return $this->status;
		}

		return $json;
	}



	/**
	 * select id
	 *
	 * @param      <type>  $_id    The identifier
	 *
	 * @return     <type>  ( description_of_the_return_value )
	 */
	public function select($_id)
	{
		$id     = is_array($_id)? join(",", $_id) : $_id;
		$path   = $this->get_path(__FUNCTION__);
		$params = array( "messageid" => $id);
		$json   = $this->execute($path, $params);

		if(!is_array($json))
		{
			return $this->status;
		}

		if(is_array($receptor))
		{
			return $json;
		}
		else
		{
			return $json[0];
		}
	}


	/**
	 * select outbox
	 *
	 * @param      <type>  $_startdate  The startdate
	 * @param      <type>  $_enddate    The enddate
	 *
	 * @return     <type>  ( description_of_the_return_value )
	 */
	public function selectoutbox($_startdate, $_enddate= null)
	{
		$path 	= $this->get_path(__FUNCTION__);
		$params	=
		[
			"startdate" => $_startdate,
			"enddate"   => $_enddate
		];

		$json 	= $this->execute($path, $params);

		if(!is_array($json))
		{
			return $this->status;
		}

		return $json;
	}


	/**
	 * { function_description }
	 *
	 * @param      integer  $_pagesize  The pagesize
	 *
	 * @return     <type>   ( description_of_the_return_value )
	 */
	public function latestoutbox($_pagesize = 10)
	{
		$path   = $this->get_path(__FUNCTION__);
		$params = array( "pagesize" => $_pagesize);
		$json   = $this->execute($path, $params);

		if(!is_array($json))
		{
			return $this->status;
		}

		return $json;
	}


	/**
	 * status of message id
	 *
	 * @param      <type>  $_id    The identifier
	 *
	 * @return     <type>  ( description_of_the_return_value )
	 */
	public function status($_id)
	{
		$id     = is_array($_id)? join(",", $_id) : $_id;
		$path   = $this->get_path(__FUNCTION__);
		$params = array( "messageid" => $id);
		$json   = $this->execute($path, $params, true);

		if(!is_array($json))
		{
			return $this->status;
		}

		if(is_array($_id))
		{
			return $json;
		}
		else
		{
			return $json[0];
		}
	}


	/**
	 * cancel message id
	 *
	 * @param      <type>  $_id    The identifier
	 *
	 * @return     <type>  ( description_of_the_return_value )
	 */
	public function cancel($_id)
	{
		$id     = is_array($_id)? join(",", $_id) : $_id;
		$path   = $this->get_path(__FUNCTION__);
		$params = array( "messageid" => $id);
		$json   = $this->execute($path, $params);

		if(!is_array($json))
		{
			return $this->status;
		}

		if(is_array($_id))
		{
			return $json;
		}
		else
		{
			return $json[0];
		}
	}


	/**
	 * get unread
	 *
	 * @param      <type>   $_linenumber  The linenumber
	 * @param      integer  $_isread      The isread
	 *
	 * @return     <type>   ( description_of_the_return_value )
	 */
	public function unreads($_linenumber= null, $_isread= 0)
	{
		$_linenumber = is_null($_linenumber)? $this->linenumber: $_linenumber;
		$path        = $this->get_path(__FUNCTION__);
		$params      =
		[
			"isread"     => $_isread,
			"linenumber" => $_linenumber
		];

		$json        = $this->execute($path, $params);

		if(!is_array($json))
		{
			return $this->status;
		}

		return $json;
	}


	/**
	 * get account info
	 *
	 * @return     <type>  ( description_of_the_return_value )
	 */
	public function account_info()
	{
		$path = $this->get_path('info','account');
		$json = $this->execute($path, null, true);

		if(!is_array($json))
		{
			return $this->status;
		}

		return $json;
	}


	/**
	 * lookup verification code
	 *
	 * @param      <type>  $_receptor  The receptor
	 * @param      <type>  $_token     The token
	 * @param      <type>  $_token2    The token 2
	 * @param      <type>  $_token3    The token 3
	 * @param      <type>  $_template  The template
	 * @param      string  $_type      The type
	 */
	public function verify($_mobile, $_token, $_token2 = null, $_token3 = null, $_template = null, $_type = 'sms')
	{

		$path = $this->get_path('lookup','verify');
		$parameters =
		[
			'receptor' => $_mobile,
			'token'    => $_token,
			'token2'   => $_token2,
			'token3'   => $_token3,
			'template' => $_template,
			'type'     => $_type,
		];

		$json = $this->execute($path, $parameters);

		if(!is_array($json))
		{
			return $this->status;
		}

		return $json;
	}

	public function tts($_mobile, $_message)
	{

		$path = $this->get_path('maketts','call');
		$parameters =
		[
			'receptor' => $_mobile,
			'message'  => $_message,
			'date'     => null,
			'localid'  => null,
			'repeat'   => null,

		];


		$json = $this->execute($path, $parameters);

		if(!is_array($json))
		{
			return $this->status;
		}

		return $json;
	}


	public function client_add($_data)
	{
		$path = $this->get_path('add','client');
		$json = $this->execute($path, $_data, true);

		if(!is_array($json))
		{
			return $this->status;
		}

		return $json;
	}


	public function client_update($_data)
	{
		$path = $this->get_path('update','client');
		$json = $this->execute($path, $_data, true);

		if(!is_array($json))
		{
			return $this->status;
		}

		return $json;
	}


	public function client_fetch($_data)
	{
		$path = $this->get_path('fetch','client');
		$json = $this->execute($path, $_data, true);

		if(!is_array($json))
		{
			return $this->status;
		}

		return $json;
	}


	public function client_fetchbylocalid($_data)
	{
		$path = $this->get_path('fetchbylocalid','client');
		$json = $this->execute($path, $_data, true);

		if(!is_array($json))
		{
			return $this->status;
		}

		return $json;
	}


	public function client_fchargecredit($_data)
	{
		$path = $this->get_path('fchargecredit','client');
		$json = $this->execute($path, $_data, true);

		if(!is_array($json))
		{
			return $this->status;
		}

		return $json;
	}


	public function client_chargecredit($_data)
	{
		$path = $this->get_path('chargecredit','client');
		$json = $this->execute($path, $_data, true);

		if(!is_array($json))
		{
			return $this->status;
		}

		return $json;
	}


	public function client_setstatus($_data)
	{
		$path = $this->get_path('setstatus','client');
		$json = $this->execute($path, $_data, true);

		if(!is_array($json))
		{
			return $this->status;
		}

		return $json;
	}


	public function client_renewkey($_data)
	{
		$path = $this->get_path('renewkey','client');
		$json = $this->execute($path, $_data, true);

		if(!is_array($json))
		{
			return $this->status;
		}

		return $json;
	}


	public function client_list()
	{
		$path = $this->get_path('list','client');
		$json = $this->execute($path, null, true);

		if(!is_array($json))
		{
			return $this->status;
		}

		return $json;
	}


	public function msg($_code)
	{
		$msg = null;
		switch ($_code)
		{
			case 200: $msg = 'درخواست تایید شد'; break;
			case 400: $msg = 'پارامترها ناقص هستند'; break;
			case 401: $msg = 'حساب کاربری غیرفعال شده است'; break;
			case 402: $msg = 'عملیات ناموفق بود'; break;
			case 403: $msg = 'کد شناسائی ( API KEY ) یا اطلاعات ورود (نام کاربری و رمز عبور ) معتبر نمی‌باشد.'; break;
			case 409: $msg = 'سرور قادر به پاسخگوئی نیست بعدا تلاش کنید'; break;
			case 411: $msg = 'دریافت کننده نامعتبر است'; break;
			case 412: $msg = 'ارسال کننده نامعتبر است'; break;
			case 413: $msg = 'پیام خالی است و یا طول پیام بیش از حد مجاز می‌باشد. لاتین 612 ﻛﺎراﻛﺘﺮ و ﻓﺎرﺳﻲ 268 ﻛﺎراﻛﺘﺮ'; break;
			case 414: $msg = 'تعداد رکورد ها بیشتر از حد مجاز است . در هر فراخوانی حداکثر 200 رکورد .'; break;
			case 417: $msg = 'تاریخ معتبر نمی‌باشد. مقدار 0 به معنای زمانی فعلی است و در غیر اینصورت فرمتUNIXTIME'; break;
			case 418: $msg = 'اعتبار حساب شما کافی نیست. لطفا برای شارژ حساب اقدام نمائید.'; break;
			case 419: $msg = 'طول آرایه متن و گیرنده ها و فرستنده ها هم اندازه نیست .'; break;
			case 502: $msg = 'نام کاربری انتخاب شده تکراری می‌باشد.'; break;
			case 503: $msg = 'شناسه داخلی انتخاب شده تکراری است (localid)'; break;
			case 504: $msg = 'تعرفه ارسال پیامک ارزان تر از تعرفه والد می‌باشد.'; break;
			case 505: $msg = 'سرویس والد منقضی شده است.(در صورتی مدت سرویس ویژه شما به اتمام رسیده باشد با این خطا روبرو خواهید شد)'; break;
			case 506: $msg = 'تغییر تعرفه در صورتی مانده اعتبار کمتر از 1000 ریال باشد امکان پذیر است.'; break;
		}

		return $msg;
	}
}
?>