<?php
namespace addons\content_enter\mobile\request;

class controller extends \addons\content_enter\main\controller
{
	/**
	 * check route of account
	 * @return [type] [description]
	 */
	function _route()
	{
			// if this step is locked go to error page and return
		if(self::lock('mobile/request'))
		{
			self::error_page('mobile/request');
			return;
		}

		// parent::_route();
		$this->get()->ALL('mobile/request');
		$this->post('mobile')->ALL('mobile/request');

	}
}
?>