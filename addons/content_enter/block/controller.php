<?php
namespace addons\content_enter\block;


class controller extends \addons\content_enter\main\controller
{
	public function _route()
	{
		// if this step is locked go to error page and return
		if(self::lock('block'))
		{
			self::error_page('block');
			return;
		}
	}
}
?>