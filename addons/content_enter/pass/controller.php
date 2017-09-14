<?php
namespace addons\content_enter\pass;


class controller extends \addons\content_enter\main\controller
{
	public function _route()
	{
		// if this step is locked go to error page and return
		if(self::lock('pass'))
		{
			self::error_page('pass');
			return;
		}

		if((self::done_step('mobile') || self::done_step('username')) && self::user_data('password'))
		{
			$this->post('check')->ALL('pass');
		}
		else
		{
			self::error_page('pass');
		}
	}
}
?>