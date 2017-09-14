<?php
namespace lib\mvc\controllers;

trait tools
{
	/**
	 * call model func and return needed option in all condition
	 * @return [type] return string or array contain option value
	 */
	public function option()
	{
		// var_dump(debug_backtrace());exit();
		return \lib\option::config(...func_get_args());
	}


	/**
	 * call permission access func
	 * @return [type] [description]
	 */
	public function access()
	{
		\lib\permission::$user_id = $this->login('id');
		return \lib\permission::access(...func_get_args());
	}


	/**
	 * convert numver to en
	 * @param  [type] $string [description]
	 * @return [type]         [description]
	 */
	function convert_Num2En($string)
	{
		$persian = array('۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹');
		$num = range(0, 9);
		return str_replace($persian, $num, $string);
	}
}
?>