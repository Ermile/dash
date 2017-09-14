<?php
namespace lib\dash;

class is
{
	public static function ajax(){
		return isset($_SERVER['HTTP_X_REQUESTED_WITH'])  && mb_strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';
	}

	public static function accept($name)
	{
		if(isset($_SERVER['HTTP_ACCEPT']))
			return (strpos($_SERVER['HTTP_ACCEPT'], $name) !== false);

		return null;
	}

	public static function json_accept(){
		$ret = self::accept("application/json");
		if($ret) return true;
		elseif(isset($_SERVER['Content-Type']) && preg_match("/application\/json/i", $_SERVER['Content-Type'])){
			return true;
		}
		return false;
	}
}
?>