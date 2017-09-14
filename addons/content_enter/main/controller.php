<?php
namespace addons\content_enter\main;

class controller extends \mvc\controller
{
	use _use;
	/**
	 * check route of account
	 * @return [type] [description]
	 */
	function _route()
	{
		$url = \lib\router::get_url();
		// /main can not route
		if($url === 'main')
		{
			\lib\error::page(T_("Unavalible"));
		}
	}


	/**
	* if the user is login redirect to base
	*/
	public function if_login_not_route()
	{
		if($this->login())
		{
			self::go_to($this->url('base'));
		}
	}


	/**
	* if login route
	*/
	public function if_login_route()
	{
		if(!$this->login())
		{
			self::go_to($this->url('base'));
		}
	}


	/**
	* check is set remeber me of this user
	*/
	public function check_remember_me()
	{
		if(\lib\db\sessions::get_cookie() && !$this->login())
		{
			$user_id = \lib\db\sessions::get_user_id();

			if($user_id && is_numeric($user_id))
			{
				// set user id in static var
				self::$user_id = $user_id;
				// load user data by user id
				self::load_user_data('user_id');
				// set login session
				self::enter_set_login(null, true);
			}
		}
	}
}
?>