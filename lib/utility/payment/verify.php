<?php
namespace lib\utility\payment;
use \lib\debug;
use \lib\option;
use \lib\utility;
use \lib\db\logs;

class verify
{
	public static $user_id = null;

	public static $log_data = null;
	/**
	 * set config
	 * set user id to save log for this user id
	 */
	public static function config()
	{
		if(!self::$user_id && isset($_SESSION['user']['id']))
		{
			self::$user_id = $_SESSION['user']['id'];
		}
	}


	/**
	 * after complete pay operation
	 * reidrect to turn_back url
	 *
	 * @param      <type>  $_transaction_id  The transaction identifier
	 */
	public static function turn_back($_transaction_id = null)
	{
		$turn_back = null;
		if($_transaction_id && isset($_SESSION['turn_back'][$_transaction_id]))
		{
			$turn_back = $_SESSION['turn_back'][$_transaction_id];
		}
		else
		{
			$host      = Protocol."://" . \lib\router::get_root_domain();
			$lang      = \lib\define::get_current_language_string();
			$turn_back =  $host;
			$turn_back .= $lang;

			if(\lib\option::config('redirect'))
			{
		        $turn_back .= '/'. \lib\option::config('redirect');
			}
		}

		if(!$turn_back)
		{
			$turn_back = Protocol."://" . \lib\router::get_root_domain();
		}

		// redirect to turn back url
        (new \lib\redirector($turn_back))->redirect();
	}

	use verify\zarinpal;
	use verify\parsian;


}
?>