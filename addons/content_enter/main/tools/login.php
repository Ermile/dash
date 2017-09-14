<?php
namespace addons\content_enter\main\tools;
use \lib\utility\visitor;
use \lib\utility;
use \lib\debug;
use \lib\db;

trait login
{
	/**
	 * find redirect url
	 *
	 * @return     <type>  ( description_of_the_return_value )
	 */
	public static function find_redirect_url($_url = null)
	{
		$host = Protocol."://" . \lib\router::get_root_domain();
		if($_url)
		{
			return $_url;
		}
		// get url language
		// if have referer redirect to referer
		if(utility::get('referer'))
		{
			$host = utility::get('referer');
		}
		elseif(self::get_enter_session('first_signup'))
		{
			$host .= \lib\define::get_current_language_string();
			// if first signup
			if(\lib\option::config('enter', 'singup_redirect'))
			{
				$host .= '/'. \lib\option::config('enter', 'singup_redirect');
			}
			else
			{
				$host .= \lib\option::config('redirect_url');
			}
		}
		else
		{

			$language = \lib\db\users::get_language(self::user_data('id'));
			if($language && \lib\utility\location\languages::check($language))
			{
				$host .= \lib\define::get_current_language_string($language);
			}
			else
			{
				$host .= \lib\define::get_current_language_string();
			}

			$host .='/'. \lib\option::config('redirect');
		}

		return $host;
	}


	/**
	 * login if have remember me
	 */
	public static function login_by_remember($_url = null)
	{
		$cookie = \lib\db\sessions::get_cookie();
		if($cookie)
		{
			$user_id = \lib\db\sessions::get_user_id();
			if($user_id)
			{
				$user_data = \lib\db\users::get_by_id($user_id);
				\lib\db\users::set_login_session($user_data);

				if(isset($_SESSION['main_account']))
				{
					// if the admin user login by this user
					// not save the session
				}
				else
				{
					\lib\db\sessions::set($user_id);
				}
			}
		}
	}


	/**
	 * login
	 */
	public static function enter_set_login($_url = null, $_auto_redirect = false)
	{

		\lib\db\users::set_login_session(self::user_data());

		if(self::user_data('id'))
		{
			if(isset($_SESSION['main_account']) && isset($_SESSION['main_mobile']))
			{
				if(isset($_SESSION['user']['mobile']) && $_SESSION['user']['mobile'] === $_SESSION['main_mobile'])
				{
					\lib\db\sessions::set(self::user_data('id'));
				}
				// if the admin user login by this user
				// not save the session
			}
			else
			{
				// set remeber and save session
				\lib\db\sessions::set(self::user_data('id'));
				// check user status
				// if the user status is awaiting
				// set the user status as enable
				if(self::user_data('status') === 'awaiting' && is_numeric(self::user_data('id')))
				{
					\lib\db\users::update(['status' => 'active'], self::user_data('id'));
				}
			}
		}

		$url = self::find_redirect_url($_url);

		if($_auto_redirect)
		{
			// go to new address
			self::go_redirect($url);
		}
		else
		{
			self::set_enter_session('redirect_url', $url);
			return $url;
		}

	}


	/**
	 * referer
	 *
	 * @param      array  $_args  The arguments
	 */
	public static function login_referer($_args = [])
	{
		\lib\debug::msg('direct', true);
		$url = $this->url("root");
		if(\lib\router::$prefix_base)
		{
			$url .= '/'.\lib\router::$prefix_base;
		}

		if(\lib\utility::get('referer'))
		{
			$url .= '/referer?to=' . \lib\utility::get('referer');
			$this->redirector($url)->redirect();
		}
		elseif(\lib\option::config('account', 'status'))
		{
			$url = $this->url("root");
			$_redirect_sub = \lib\option::config('redirect');

			if($_redirect_sub !== 'home')
			{
				// if(\lib\option::config('fake_sub'))
				// {
				// 	echo $this->redirector()->set_subdomain()->set_url($_redirect_sub)->redirect();
				// }
				// else
				// {
				//
				// }

				$url .= '/'. $_redirect_sub;

				if(isset($_args['user_id']) && $_args['user_id'])
				{
					$language = \lib\db\users::get_language($_args['user_id']);
					if($language && \lib\utility\location\languages::check($language))
					{
						$url .= \lib\define::get_current_language_string($language);
					}

				}
				$this->redirector($url)->redirect();
			}
		}
		$this->redirector()->set_domain()->set_url()->redirect();
	}


	/**
	 * sync guest data to new login
	 */
	public static function sync_guest()
	{
		$old_user_id = $this->login('id');
		$new_user_id = self::user_data('id');

		if(intval($old_user_id) === intval($new_user_id))
		{
			\lib\db\logs::set('enter:guest:userid:is:guestid', self::user_data('id'), $log_meta);
			return;
		}

		\lib\utility\sync::web_guest($new_user_id, $old_user_id);
	}


	/**
	 * the gues has login
	 * logout the guest
	 * sync guset by new user
	 * login new user
	 *
	 * @param      <type>  $_url   The url
	 */
	public static function login_set_guest($_url = null)
	{
		$log_meta =
		[
			'data' => null,
			'meta' =>
			[
				'mobile'  => $this->mobile,
				'user_id' => self::user_data('id'),
				'input'   => utility::post(),
				'session' => $_SESSION,
			]
		];

		$status = \lib\db\users::get_status(self::user_data('id'));
		switch ($status)
		{
			case 'active':
				\lib\db\logs::set('enter:guest:have:active:user', self::user_data('id'), $log_meta);
				break;
			case 'awaiting':
				$this->sync_guest();
				break;

			default:
				\lib\db\logs::set('enter:guest:invalid:status', self::user_data('id'), $log_meta);
				break;
		}
		// distroy guest session to set new session
		\lib\db\sessions::logout(self::user_data('id'));
		$this->put_logout();

	}


	/**
	 * Sets the logout.
	 *
	 * @param      <type>  $_user_id  The user identifier
	 */
	public static function set_logout($_user_id, $_auto_redirect = true)
	{

		if($_user_id && is_numeric($_user_id))
		{
			if(isset($_SESSION['main_account']) && isset($_SESSION['main_mobile']) && isset($_SESSION['user']['mobile']))
			{
				if($_SESSION['user']['mobile'] === $_SESSION['main_mobile'])
				{
					\lib\db\sessions::logout($_user_id);
				}
				// if the admin user login by this user
				// not save the session
			}
			else
			{
				// set this session as logout
				\lib\db\sessions::logout($_user_id);
			}
		}

		$_SESSION['user'] = [];

		if(isset($_SESSION['main_account']) && isset($_SESSION['main_mobile']))
		{
			if(isset($_SESSION['user']['mobile']) && $_SESSION['user']['mobile'] !== $_SESSION['main_mobile'])
			{
				$host = Protocol."://" . \lib\router::get_root_domain();
				$host .= \lib\define::get_current_language_string();
				$host .= '/enter?mobile='. (string) $_SESSION['main_mobile'];
				$redirect = new \lib\redirector($host);
				$redirect->redirect();
				return;
			}
		}

		// unset and destroy session then regenerate it
		// session_unset();
		// if(session_status() === PHP_SESSION_ACTIVE)
		// {
		// 	session_destroy();
		// 	// session_regenerate_id(true);
		// }

		if($_auto_redirect)
		{
			// go to base
			self::go_to('main');
		}

	}
}
?>