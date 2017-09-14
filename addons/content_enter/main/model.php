<?php
namespace addons\content_enter\main;


class model extends \mvc\model
{

	use _use;

	/**
	 * load some data from options
	 */
	public function _construct()
	{
		// get the enter way sorting from options
		if(\lib\option::config('enter', 'send_rate') && is_array(\lib\option::config('enter', 'send_rate')))
		{
			self::$send_rate = \lib\option::config('enter', 'send_rate');
		}

		// get resend rate code from lib option
		if(\lib\option::config('enter', 'resend_rate') && is_array(\lib\option::config('enter', 'resend_rate')))
		{
			self::$resend_rate = \lib\option::config('enter', 'resend_rate');
		}

		// get sms rate
		if(\lib\option::config('enter', 'sms_rate') && is_array(\lib\option::config('enter', 'sms_rate')))
		{
			self::$sms_rate = \lib\option::config('enter', 'sms_rate');
		}

		// get block status
		if(\lib\option::config('enter', 'block_status') && is_array(\lib\option::config('enter', 'block_status')))
		{
			self::$block_status = \lib\option::config('enter', 'block_status');
		}

		// in dev mode
		if(Tld === 'dev')
		{
			self::$dev_mode = true;
		}
		// load parent::_construct if exist
		if(method_exists('parent', '_construct'))
		{
			parent::_construct();
		}
	}

}
?>