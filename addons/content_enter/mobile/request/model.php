<?php
namespace addons\content_enter\mobile\request;
use \lib\utility;
use \lib\debug;
use \lib\db;

class model extends \addons\content_enter\main\model
{
	// load mobile data
	public $mobile_data = [];

	public $temp_mobile = [];


	/**
	 * Gets the enter.
	 *
	 * @param      <type>  $_args  The arguments
	 */
	public function post_mobile($_args)
	{
		if(utility::post('will') === 'no')
		{
			// the user dont whill to enter mobile :/
			// never ask this question at this user
			self::set_enter_session('dont_will_set_mobile', true);

			self::mobile_request_next_step();

			return;
		}

		if(!utility::post('mobile'))
		{
			debug::error(T_("Please enter mobile or skip this step"));
			return false;
		}

		$mobile = utility\filter::mobile(utility::post('mobile'));

		if(!$mobile)
		{
			debug::error(T_("Please enter a valid mobile number"));
			return false;
		}

		self::set_enter_session('verify_from', 'mobile_request');
		self::set_enter_session('temp_mobile', $mobile);
		self::send_code_way();
		return;
	}
}
?>