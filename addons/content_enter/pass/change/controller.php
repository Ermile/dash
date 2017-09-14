<?php
namespace addons\content_enter\pass\change;

class controller extends \addons\content_enter\main\controller
{
	/**
	 * check route of account
	 * @return [type] [description]
	 */
	function _route()
	{

		// if this step is locked go to error page and return
		if(self::lock('pass/change'))
		{
			self::error_page('pass/change');
			return;
		}
		// if the user is login redirect to base
		parent::if_login_route();


		$this->get()->ALL('pass/change');
		$this->post('pass')->ALL('pass/change');

	}
}
?>