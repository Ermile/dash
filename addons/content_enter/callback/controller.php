<?php
namespace addons\content_enter\callback;
use \lib\debug;
use \lib\utility;

class controller extends \addons\content_enter\main\controller
{
	public function _route()
	{
		// 10002000200251


		if(!utility::get('service') || utility::get('uid') != '201708111')
		{
			\lib\error::page(T_("Invalid url"));
		}

		switch (utility::get('service'))
		{
			case 'kavenegar':
				$this->model()->kavenegar();
				break;

			default:
				\lib\error::page(T_("Invalid service"));
				break;
		}
	}
}
?>