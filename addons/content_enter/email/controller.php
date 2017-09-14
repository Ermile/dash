<?php
namespace addons\content_enter\email;


class controller extends \addons\content_enter\main\controller
{
	public function _route()
	{
		// if the user is login redirect to base
		parent::if_login_not_route();

		// check remeber me is set
		// if remeber me is set: login!
		parent::check_remember_me();
	}
}
?>