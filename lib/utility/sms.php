<?php
namespace dash\utility;
require(lib."utility/kavenegar_api.php");

/** Sms management class **/
class sms
{
	/**
	 * Makes a message.
	 *
	 * @param      <type>  $_message  The message
	 *
	 * @return     string  ( description_of_the_return_value )
	 */
	private static function make_message($_message, $_options = [])
	{
		$default_option =
		[
			'header'         => true,
			'footer'         => true,
		];

		if(!is_array($_options))
		{
			$_options = [];
		}

		$_options = array_merge($default_option, $_options);

		$_message = trim($_message);

		// create complete message
		$sms_header = T_(trim(\dash\option::sms('kavenegar', 'header')));
		$sms_footer = T_(trim(\dash\option::sms('kavenegar', 'footer')));

		$message = '';

		if($sms_header && $_options['header'])
		{
			$message    .= $sms_header;
			$message    .= "\n\n";
		}

		$message .= $_message;

		if($sms_footer && $_options['footer'])
		{
			$message    .= "\n\n";
			$message    .= $sms_footer;
		}

		if(\dash\option::sms('kavenegar', 'one') && mb_strlen($message) > self::is_rtl($message, true))
		{
			if($sms_header && $_options['header'])
			{
				$message = $sms_header. "\n\n". $_message;
			}

			if(\dash\option::sms('kavenegar', 'one') && mb_strlen($message) > self::is_rtl($message, true))
			{
				$message = $_message;
			}
		}
		return $message;
	}

	/**
	 * send sms
	 *
	 * @param      <type>     $_mobile   The mobile
	 * @param      <type>     $_message  The message
	 * @param      array      $_options  The options
	 *
	 * @return     \|boolean  ( description_of_the_return_value )
	 */
	public static function send($_mobile, $_message, $_options = [])
	{
		if(!$_mobile || !$_message || !trim($_message))
		{
			return null;
		}

		// send by special subdomain
		if(\dash\option::config('special_sms_provider'))
		{
			if(is_callable(['\\lib\\sms', 'send']))
			{
				return call_user_func(['\\lib\\sms', 'send'], ...func_get_args());
			}
			else
			{
				\dash\notif::error('Invalid SMS provider');
				return false;
			}
		}

		// disable status
		// sms sevice is locked
		if(!\dash\option::sms('kavenegar', 'status'))
		{
			return false;
		}

		// cehck api key
		$api_key = \dash\option::sms('kavenegar','apikey');
		if(!$api_key)
		{
			return false;
		}

		$default_option =
		[
			'line'    => \dash\option::sms('kavenegar', 'line'),
			'type'    => 1,
			'date'    => 0,
			'localid' => null,
			'header'  => true,
			'footer'  => true,
		];

		if(!is_array($_options))
		{
			$_options = [];
		}

		$_options = array_merge($default_option, $_options);


		$mobile = \dash\utility\filter::mobile($_mobile);
		if(!$mobile)
		{
			return false;
		}

		if(\dash\option::sms('kavenegar', 'iran') && substr($mobile, 0, 2) !== '98')
		{
			return false;
		}

		$message = self::make_message($_message, $_options);
		\dash\log::set('smsSend');

		// send sms
		$api    = new \dash\utility\kavenegar_api($api_key, $_options['line']);
		$result = $api->send($mobile, $message, $_options['type'], $_options['date'], $_options['localid']);

		// success result
		// {
		// 	"messageid": 753233381,
		// 	"message": "sms text",
		// 	"status": 5,
		// 	"statustext": "ارسال به مخابرات",
		// 	"sender": "10006660066600",
		// 	"receptor": "09109610612",
		// 	"date": 1565518264,
		// 	"cost": 180
		// }
		return $result;

	}

	/**
	 * check the input is rtl or not
	 * @param  [type]  $string [description]
	 * @param  [type]  $type   [description]
	 * @return boolean         [description]
	 */
	private static function is_rtl($_str, $_type = false)
	{
		$rtl_chars_pattern = '/[\x{0590}-\x{05ff}\x{0600}-\x{06ff}]/u';
		$result            = preg_match($rtl_chars_pattern, $_str);
		if($_type)
		{
			$result = $result? 70: 160;
		}
		return $result;
	}


	/**
	 * Sends an array.
	 * send one message to multi mobile
	 *
	 * @param      <type>     $_mobiles  The mobiles
	 * @param      <type>     $_message  The message
	 * @param      array      $_options  The options
	 *
	 * @return     \|boolean  ( description_of_the_return_value )
	 */
	public static function send_array($_mobiles, $_message, $_options = [])
	{
		if(!$_mobiles || !$_message || !is_array($_mobiles))
		{
			return null;
		}

		// send by special subdomain
		if(\dash\option::config('special_sms_provider'))
		{
			if(is_callable(['\\lib\\sms', 'send_array']))
			{
				return call_user_func(['\\lib\\sms', 'send_array'], ...func_get_args());
			}
			else
			{
				\dash\notif::error('Invalid SMS provider');
				return false;
			}
		}

		// disable status
		// sms sevice is locked
		if(!\dash\option::sms('kavenegar', 'status'))
		{
			return false;
		}

		// cehck api key
		$api_key = \dash\option::sms('kavenegar','apikey');
		if(!$api_key)
		{
			return false;
		}

		$default_option =
		[
			'line'   => \dash\option::sms('kavenegar', 'line'),
			'type'   => 1,
			'date'   => 0,
			'header' => true,
			'footer' => true,
		];

		if(!is_array($_options))
		{
			$_options = [];
		}

		$_options = array_merge($default_option, $_options);

		$accepted_mobile = [];
		foreach ($_mobiles as $key => $value)
		{
			$mobile = \dash\utility\filter::mobile($value);

			if($mobile)
			{
				if(\dash\option::sms('kavenegar', 'iran') && substr($mobile, 0, 2) !== '98')
				{
					continue;
				}
				array_push($accepted_mobile, $value);
			}
		}

		$accepted_mobile = array_filter($accepted_mobile);
		$accepted_mobile = array_unique($accepted_mobile);

		if(empty($accepted_mobile))
		{
			return null;
		}

		$result  = [];
		$message = self::make_message($_message, $_options);
		$api     = new \dash\utility\kavenegar_api($api_key, $_options['line']);
		\dash\log::set('smsSendArray', ['count_send' => count($accepted_mobile)]);
		$chunk   = array_chunk($accepted_mobile, 200);
		foreach ($chunk as $key => $last_200_mobile)
		{
			$result[] = $api->sendarray($_options['line'], $last_200_mobile, $message, $_options['type'], $_options['date']);
		}

		return $result;
	}


	public static function info()
	{
		// send by special subdomain
		if(\dash\option::config('special_sms_provider'))
		{
			if(is_callable(['\\lib\\sms', 'info']))
			{
				return call_user_func(['\\lib\\sms', 'info'], ...func_get_args());
			}
			else
			{
				\dash\notif::error('Invalid SMS provider');
				return false;
			}
		}
		// disable status
		// sms sevice is locked
		if(!\dash\option::sms('kavenegar', 'status'))
		{
			return false;
		}

		// cehck api key
		$api_key = \dash\option::sms('kavenegar','apikey');
		if(!$api_key)
		{
			return false;
		}

		$api    = new \dash\utility\kavenegar_api($api_key);
		$result = $api->account_info();
		return $result;
	}
}
?>