<?php
namespace addons\content_enter\delete\request;

class controller extends \addons\content_enter\main\controller
{
	/**
	 * check route of account
	 * @return [type] [description]
	 */
	function _route()
	{
		// if this step is locked go to error page and return
		if(self::lock('delete/request'))
		{
			self::error_page('delete/request');
			return;
		}


		$this->get()->ALL('delete/request');
		$this->post('delete')->ALL('delete/request');

	}
}
?>