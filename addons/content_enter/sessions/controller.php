<?php
namespace addons\content_enter\sessions;

class controller extends \addons\content_enter\main\controller
{
	/**
	 * check route of account
	 * @return [type] [description]
	 */
	function _route()
	{
		// if user was login route this page
		parent::if_login_route();

		if(self::get_request_method() === 'get')
		{
			$this->get(false, 'sessions')->ALL();
		}
		elseif(self::get_request_method() === 'post')
		{
			$this->post('sessions')->ALL();
		}
		else
		{
			self::error_method('sessions');
		}
	}
}
?>