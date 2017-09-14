<?php
namespace addons\content_enter\email\set;
use \lib\utility;
use \lib\debug;
use \lib\db;

class model extends \addons\content_enter\main\model
{

	/**
	 * Posts an enter.
	 *
	 * @param      <type>  $_args  The arguments
	 */
	public function post_email($_args)
	{
		if(utility::post('email'))
		{
			self::set_enter_session('temp_email', utility::post('email'));
		}
		else
		{
			// plus count invalid emailword
			self::plus_try_session('no_email_send_set');

			debug::error(T_("No email was send"));
			return false;
		}

		// set session verify_from set
		self::set_enter_session('verify_from', 'email_set');

		// send code whit email
		self::send_code_email();
	}
}
?>