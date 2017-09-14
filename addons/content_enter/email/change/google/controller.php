<?php
namespace addons\content_enter\email\change\google;

class controller extends \addons\content_enter\main\controller
{
	/**
	 * check route of account
	 * @return [type] [description]
	 */
	function _route()
	{
		$url = \lib\router::get_url();
		// if(self::lock('email/change/google'))
		// {
		// 	self::error_page('email/change/google');
		// 	return;
		// }

		$this->get()->ALL('email/change/google');
		$this->post('change_google')->ALL('email/change/google');

	}
}
?>