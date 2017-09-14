<?php
namespace addons\content_enter\email\set;

class controller extends \addons\content_enter\main\controller
{
	/**
	 * check route of account
	 * @return [type] [description]
	 */
	function _route()
	{
		// if the user is login redirect to base
		parent::if_login_route();

		// parent::_route();
		// if the user have email can not set email again
		// he must change her email
		if($this->login('email'))
		{
			$this->redirector()->set_domain()->set_url('enter/email/change')->redirect();
			return;
		}

		$this->get()->ALL('email/set');

		$this->post('email')->ALL('email/set');

	}
}
?>