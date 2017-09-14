<?php
namespace addons\content_enter\verify;


class controller extends \addons\content_enter\main\controller
{
	public function _route()
	{
		// if the user is login redirect to base
		parent::if_login_not_route();

		$url = \lib\router::get_url();

		if($url === 'verify')
		{
			self::error_page('verify');
		}
	}
}
?>