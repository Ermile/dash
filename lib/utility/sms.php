<?php
namespace lib\utility;
require(lib."utility/kavenegar_api.php");

/** Sms management class **/
class sms
{
	/**
	 * Create a text message and send it to user mobile number
	 * @param  [type] $_mobile [description]
	 * @param  [type] $_msg    [description]
	 * @param  [type] $_arg    [description]
	 * @return [type]          [description]
	 */
	public static function send(array $_options, $_request = 'send')
	{
		$api_settings = [];
		// declare variables
		$tmp_obj     = \lib\main::$controller;
		$settings    = \lib\option::sms('kavenegar');

		$api_settings['global_settings'] = $settings;
		$api_settings['request'] = $_request;
		// if sms service is disable, go out
		if(!isset($settings['status']) || (isset($settings['status']) && !$settings['status']))
		{
			return false;
		}
		// set restriction
		if(isset($settings['iran']) && $settings['iran'] &&
			substr($_options['mobile'], 0, 2) !== '98')
		{
			self::error(T_("We can't give service to this number"));
			self::error(T_("now we only support Iran!"));
			return false;
		}

		// get sms service name and if not exist show related msg
		if(isset($settings['value']))
		{
			$sms_service = $settings['value'];
			if(!method_exists(__CLASS__, $sms_service))
			{
				self::error(T_('This sms service is unavailable'), 'error');
				return false;
			}
		}
		else
		{
			self::error(T_('This sms service is unavailable'), 'error');
			return false;
		}

		if(isset($settings['debug']) && $settings['debug'])
		{
			$api_settings['debug'] = true;
		}

		if(isset($settings['apikey']) && $settings['apikey'])
		{
			$api_settings['api_key'] = $settings['apikey'];
		}

		// call related service with special parameters
		$result = self::{$sms_service}(['args' => $_options, 'settings' => $api_settings]);
		return $result;
	}


	/**
	 * create special message depending on settings
	 * @param  [type] $_msg      [description]
	 * @param  [type] $_arg      [description]
	 * @param  [type] $_settings [description]
	 * @return [type]            [description]
	 */
	private static function message($_msg = null, $_arg = null, $_settings = null)
	{
		$_arg         = trim($_arg);
		$sms_msg      = null;
		$sms_header   = null;
		$sms_footer   = null;
		$sms_maxOne   = 160;
		$sms_forceOne = null;
		$sms_template = ['signup', 'recovery', 'verification', 'changepass'];
		$template     = null;
		// if user want one of our template create message automatically
		if(in_array($_msg, $sms_template))
		{
			$template = $_msg;
		}
		// else if msg is empty five it automatically
		elseif(!$_msg)
		{
			$template = is_null($_msg)? \lib\router::get_url(): $_msg;
		}


		// set message header
		if(isset($_settings['meta']['header']) && $_settings['meta']['header'])
		{
			$sms_header = T_($_settings['meta']['header']);
		}
		// set message footer
		if(isset($_settings['meta']['footer']) && $_settings['meta']['footer'])
		{
			$sms_footer = T_($_settings['meta']['footer']);
		}
		// set message footer
		if(isset($_settings['meta']['one']) && $_settings['meta']['one'])
		{
			$sms_forceOne = $_settings['meta']['one'];
		}
		// if user want our template
		if($template)
		{
			// if user want to send message for this template
			// then create related message
			if(isset($_settings['meta'][$template]))
			{
				// set related message depending on status passed
				switch ($template)
				{
					case 'signup':
						$_msg = T_('Your verification code is'). ' '. $_arg;
						break;

					case 'recovery':
						$_msg = T_('Your recovery code is'). ' '. $_arg;
						break;

					case 'verification':
						$_msg = T_('You account is verified successfully');
						break;

					case 'changepass':
						$_msg = T_('Your password is changed successfully');
						break;
				}
			}
			// else if send permisson is off
			else
			{
				return false;
			}
		}
		else
		{
			// else if possible translate user message
			$_msg = T_($_msg);
		}
		$_msg = trim($_msg);
		// if message is not set then return false
		if(!$_msg)
		{
			return false;
		}

		// create complete message
		$sms_msg    = $sms_header. "\n". $_msg. "\n\n". $sms_footer;

		if($sms_forceOne && mb_strlen($sms_msg) > self::is_rtl($sms_msg, true))
		{
			// create complete message
			$sms_msg    = $sms_header. "\n". $_msg;
			if($sms_forceOne && mb_strlen($sms_msg) > self::is_rtl($sms_msg, true))
			{
				// create complete message
				$sms_msg    = $_msg;
			}
		}

		// return final message:)
		return $sms_msg;
	}


	/**
	 * call kavenegar sms api
	 * @param  [type]  $_mobile [description]
	 * @param  [type]  $_msg    [description]
	 * @param  integer $_type   [description]
	 * @return [type]           [description]
	 */
	private static function kavenegar_api($_options)
	{
		$api_settings 	= $_options['settings'];
		$settings    	= $api_settings['global_settings'];
		$options 		= $_options['args'];
		if(isset($settings['line1']) && $settings['line1'])
		{
			$sms_line = $settings['line1'];
		}

		switch ($api_settings['request']) {
			case 'send':
				// set message and call related sms service
				$options['msg'] = self::message($options['msg'], $options['args'], $settings);
				if(!$options['msg'])
				{
					// message is empty
					return false;
				}

				break;
		}

		if(array_key_exists('debug', $api_settings) && $api_settings['debug'] === true)
		{
			if(isset($_options['args']['mobile']) && isset($_options['settings']['request']) && isset($_options['args']['token']))
			{
				self::error(T_($_options['settings']['request']). T_(' to '). $_options['args']['mobile'], 'true');
				self::error(T_($_options['args']['token']), 'true');
			}
			return 'debug';
		}
		if(!$api_settings['api_key'] || !$sms_line )
		{
			self::error(T_('Please set apikey and linenumber'), 'error');
			return 'debug';
		}
		// create new instance from kavenegar api and call requested func of it
		$api    = new \lib\utility\kavenegar_api($api_settings['api_key'], $sms_line);

		$result = $api->{$api_settings['request']}($options);

		// $result = $api->select(27657835);
		// $result = $api->cancel(27657835);
		// $result = $api->selectoutbox(1410570000);
		// $result = $api->account_info();
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
	 * show special error to user depending on status of debug
	 * @param  [type] $_err  [description]
	 * @param  string $_type [description]
	 * @return [type]        [description]
	 */
	private static function error($_err, $_type = 'warn')
	{
		\lib\debug::{$_type}($_err);

		// if(DEBUG)
		// {
		// 	\lib\debug::{$_type}($_err);
		// }
	}


	public static function valid_number($_mobile)
	{

	}

	public static function verification($_mobile, $_args = [])
	{

	}
}
?>