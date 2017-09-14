<?php
namespace addons\content_enter\pass\signup;

class controller extends \addons\content_enter\main\controller
{
	/**
	 * check route of account
	 * @return [type] [description]
	 */
	function _route()
	{

		// if this step is locked go to error page and return
		if(self::lock('pass/signup'))
		{
			self::error_page('pass/signup');
			return;
		}

		// if step mobile is done
		if(self::done_step('mobile') && !self::user_data('password'))
		{
			// parent::_route();
			$this->get('pass')->ALL('pass/signup');
			$this->post('pass')->ALL('pass/signup');
		}
		else
		{
			// make page error or redirect
			self::error_page('pass/signup');
		}
	}
}
?>