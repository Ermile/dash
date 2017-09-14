<?php
namespace addons\content_enter\home;
use \lib\utility;
use \lib\debug;
use \lib\db;

class model extends \addons\content_enter\main\model
{
	public function login_another_session()
	{
		// var_dump(\lib\permission::access('enter:another:session'));exit();
		if(\lib\permission::access('enter:another:session'))
		{
			$user_id = null;

			if(utility::post('mobile') !== $this->login('mobile') && !utility::get('userid'))
			{
				$user_data = \lib\db\users::get_by_mobile(\lib\utility\filter::mobile(utility::post('mobile')));

				if(isset($user_data['id']))
				{
					$user_id = $user_data['id'];
				}
				else
				{
					debug::error(T_("Mobile not found"));
					return false;
				}
			}

			if(!$user_id && utility::get('userid'))
			{
				$user_id = utility::get('userid');
			}

			if($user_id)
			{

				$main_account = $this->login('id');
				$main_mobile  = $this->login('mobile');

				if(!\lib\db\users::get_by_id($user_id))
				{
					debug::error(T_("User not found"));
					return false;
				}

				// clean existing session
				self::clean_session();
				unset($_SESSION['user']);
				unset($_SESSION['permission']);

				self::$user_id = $user_id;
				self::load_user_data('user_id');

				$_SESSION['main_account'] = $main_account;
				$_SESSION['main_mobile']  = $main_mobile;

				// set login session
				$redirect_url = self::enter_set_login();
				// save redirect url in session to get from okay page
				self::set_enter_session('redirect_url', $redirect_url);
				// set okay as next step
				self::next_step('okay');
				// go to okay page
				self::go_to('okay');
				return true;
			}
			return false;
		}
		return false;
	}



	/**
	 * Posts an enter.
	 *
	 * @param      <type>  $_args  The arguments
	 */
	public function post_enter($_args)
	{

		// get saved mobile in session to find count of change mobile
		$old_mobile = self::get_enter_session('mobile');

		// clean existing session
		self::clean_session();

		// check inup is ok
		if(!self::check_input('mobile'))
		{
			debug::error(T_("Dont!"));
			return false;
		}

		/**
		 * check login by another session
		 */
		if($this->login_another_session())
		{
			return;
		}

		// check syntax of mobile is true
		if($mobile = utility\filter::mobile(utility::post('mobile')))
		{
			self::$mobile = $mobile;
		}
		else
		{
			// plus count invalid mobile
			self::plus_try_session('invalid_mobile');
			// make error
			debug::error(T_("Invalid Mobile"), 'mobile', 'arguments');
			return false;
		}

		// if old mobile is different by new mobile
		// save in session this user change the mobile
		if($old_mobile && self::$mobile != $old_mobile)
		{
			self::plus_try_session('diffrent_mobile');
		}

		// set posted mobile in SESSION
		self::set_enter_session('mobile', self::$mobile);

		// load user data by mobile
		$user_data = self::user_data();

		// set this step is done
		self::set_step_session('mobile', true);

		// the user not found must be signup
		if(!$user_data)
		{
			// signup new user
			self::signup();

			// lock all step and set just this page to load
			self::next_step('pass/signup');

			// got to pass/signupt to get password from user
			self::go_to('pass/signup');
		}
		else
		{
			// if this user is blocked or filtered go to block page
			if(in_array(self::user_data('status'), self::$block_status))
			{
				// block page
				self::next_step('block');
				// go to block page
				self::go_to('block');
				return;
			}

			// the password field is empty
			if(!self::user_data('password'))
			{
				// lock all step and set just this page to load
				self::next_step('pass/set');

				// go to pass/set to get password from user
				self::go_to('pass/set');
			}
			else
			{
				// lock all step and set just this page to load
				self::next_step('pass');
				// open lock pass/recovery
				self::open_lock('pass/recovery');
				// go to pass to check password
				self::go_to('pass');
			}
		}
	}
}
?>