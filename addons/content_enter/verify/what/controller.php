<?php
namespace addons\content_enter\verify\what;


class controller extends \addons\content_enter\main\controller
{
	public function _route()
	{

		// if this step is locked go to error page and return
		if(self::lock('verify/what'))
		{
			self::error_page('verify/what');
			return;
		}

		if(!self::loaded_module('verify/what'))
		{
			self::loaded_module('verify/what', true);
			if(self::get_enter_session('verification_code_id') && is_numeric(self::get_enter_session('verification_code_id')))
			{
				\lib\db\logs::update(['status' => 'expire'], self::get_enter_session('verification_code_id'));
			}
		}

		$this->get()->ALL('verify/what');
	}
}
?>