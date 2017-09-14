<?php
namespace addons\content_enter\verify\email;


class controller extends \addons\content_enter\main\controller
{
	public function _route()
	{

		// if this step is locked go to error page and return
		if(self::lock('verify/email'))
		{
			self::error_page('verify/email');
			return;
		}

		$this->get()->ALL('verify/email');
	}
}
?>