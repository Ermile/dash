<?php
namespace addons\content_enter\username;
use \lib\utility;
use \lib\debug;

class model extends \addons\content_enter\main\model
{
	public function post_username()
	{
		if(!self::check_password_is_null())
		{
			debug::error(T_("Dont!"));
			return false;
		}
		// get user name
		$username = utility::post('username');
		// check user name is fill
		if(!$username)
		{
			debug::error(T_("Please fill the username field"), 'username');
			return false;
		}

		// clean session
		self::clean_session();

		// set username
		self::$username = $username;
		// load userdata by username
		self::load_user_data('username');

		// save username in $_SESSION['username']['temp_username']
		self::set_session('username', 'temp_username', $username);

		// check username exist
		if(!self::user_data() || !self::user_data('id'))
		{
			self::plus_try_session('invalid_username');

			debug::error(T_("Username not found"));
			return false;
		}
		elseif(!self::user_data('password'))
		{
			// BUG username set and password is not set

			// log meta
			$log_meta =
			[
				'meta' =>
				[
					'session'   => $_SESSION,
					'user_data' => self::user_data(),
				],
			];
			\lib\db\logs::set('enter:username:set:password:notset', self::user_data('id'), $log_meta);
			// go to mobile
			self::go_to('base');
		}
		else
		{
			// user enter by username
			// we need to her mobile to recovery this
			if(!self::get_enter_session('mobile') && self::user_data('mobile'))
			{
				self::set_enter_session('mobile', self::user_data('mobile'));
			}

			// set step session
			self::set_step_session('username', true);

			// open this pages after this page
			self::next_step('pass');
			// open lock pass/recovery to load it
			self::open_lock('pass/recovery');

			// go to pass page
			self::go_to('pass');
		}

	}
}
?>