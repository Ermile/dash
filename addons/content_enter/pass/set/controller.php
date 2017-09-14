<?php
namespace addons\content_enter\pass\set;

class controller extends \addons\content_enter\main\controller
{
	/**
	 * check route of account
	 * @return [type] [description]
	 */
	function _route()
	{
			// if this step is locked go to error page and return
		if(self::lock('pass/set'))
		{
			self::error_page('pass/set');
			return;
		}

		// if step mobile is done
		if(self::done_step('mobile') && !self::user_data('password'))
		{
			// parent::_route();
			$this->get('pass')->ALL('pass/set');
			$this->post('pass')->ALL('pass/set');
		}
		else
		{
			// make page error or redirect
			self::error_page('pass/set');
		}
	}
}
?>