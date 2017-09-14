<?php
namespace addons\content_enter\email\change;

class controller extends \addons\content_enter\main\controller
{
	/**
	 * check route of account
	 * @return [type] [description]
	 */
	function _route()
	{
		$url = \lib\router::get_url();
		if($url === 'email/change/google')
		{
			\lib\router::set_controller("\\addons\content_enter\\email\\change\\google\\controller");
			return;
		}

		// if the user is login redirect to base
		parent::if_login_route();

		// if the user have not an email can not change her email
		// he must set email
		if(!$this->login('email'))
		{
			$this->redirector()->set_domain()->set_url('enter/email/set')->redirect();
			return;
		}

		$this->get()->ALL('email/change');
		$this->post('change')->ALL('email/change');

	}
}
?>