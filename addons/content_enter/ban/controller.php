<?php
namespace addons\content_enter\ban;


class controller extends \addons\content_enter\main\controller
{
	public function _route()
	{
		// if this step is locked go to error page and return
		if(self::lock('ban'))
		{
			self::error_page('ban');
			return;
		}
	}
}
?>