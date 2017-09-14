<?php
namespace addons\content_enter\main\tools;
use \lib\utility;
use \lib\debug;

trait user_data
{

	/**
	 * Loads an user data.
	 */
	public static function load_user_data($_type = 'mobile', $_option = [])
	{
		$default_option =
		[
			'email_field' => 'googlemail',
		];

		if(!is_array($_option))
		{
			$_option = [];
		}
		$_option = array_merge($default_option, $_option);

		$data = [];

		switch ($_type)
		{
			// get user data by mobile
			case 'mobile':
				if(self::$mobile)
				{
					$data = \lib\db\users::get_by_mobile(self::$mobile);
				}
				break;

			// get use data by username
			case 'username':
				if(self::$username)
				{
					$data = \lib\db\users::get_by_username(self::$username);
				}
				break;

			// get user data by user id
			case 'user_id':
				if(self::$user_id)
				{
					$data = \lib\db\users::get_by_id(self::$user_id);
				}
				break;

			// get use data by email
			case 'email':
				if(self::$email)
				{
					$data = \lib\db\users::get_by_email(self::$email, $_option['email_field']);
				}
				break;

			default:
				# code...
				break;
		}

		if($data)
		{
			$_SESSION['enter']['user_data'] = $data;
		}
	}


	/**
	 * { function_description }
	 *
	 * @param      <type>  $_key   The key
	 */
	public static function user_data($_key = null)
	{
		if(!isset($_SESSION['enter']['user_data']))
		{
			self::load_user_data();
		}

		if($_key)
		{
			if(isset($_SESSION['enter']['user_data'][$_key]))
			{
				return $_SESSION['enter']['user_data'][$_key];
			}
			return null;
		}

		if(isset($_SESSION['enter']['user_data']))
		{
			return $_SESSION['enter']['user_data'];
		}
		return null;
	}


	/**
	*	Signup new user
	*/
	public static function signup($_args = [])
	{

		$default_args =
		[
			'mobile'      => null,
			'displayname' => null,
			'password'        => null,
			'email'       => null,
			'status'      => 'awaiting'
		];

		if(is_array($_args))
		{
			$_args = array_merge($default_args, $_args);
		}

		self::set_enter_session('first_signup', true);

		$mobile = self::get_enter_session('mobile');
		if($mobile)
		{
			$update_user = [];
			if(isset($_args['googlemail']))
			{
				$update_user['googlemail'] = $_args['googlemail'];
			}
			// set mobile to use in other function
			self::$mobile         = $mobile;
			$_args['mobile'] = $mobile;
			$_args['email']  = self::$email;

			$user_id = \lib\db\users::signup_quick($_args);

			if($user_id)
			{
				if(!empty($update_user))
				{
					\lib\db\users::update($update_user, $user_id);
				}
				self::load_user_data();
			}
			return $user_id;
		}
	}


	/**
	*	Signup new user
	*/
	public static function signup_email($_args = [])
	{
		if(self::get_enter_session('dont_will_set_mobile'))
		{
			$_args['dontwillsetmobile'] = date("Y-m-d H:i:s");
		}
		else
		{
			if(self::get_enter_session('temp_mobile') && !isset($_args['mobile']))
			{
				$_args['mobile'] = self::get_enter_session('temp_mobile');
			}
		}

		self::set_enter_session('first_signup', true);

		$user_id = \lib\db\users::insert($_args);

		if($user_id)
		{
			self::$user_id = \lib\db::insert_id();
			self::load_user_data('user_id');
		}
		return self::$user_id;

	}
}
?>