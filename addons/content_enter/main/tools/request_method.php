<?php
namespace addons\content_enter\main\tools;
use \lib\utility;
use \lib\debug;

trait request_method
{

	/**
	 * Gets the request method.
	 *
	 * @return     <type>  The request method.
	 */
	public static function get_request_method()
	{
		if(isset($_SERVER['REQUEST_METHOD']) && is_string($_SERVER['REQUEST_METHOD']))
		{
			return \lib\utility\safe::safe(mb_strtolower($_SERVER['REQUEST_METHOD']));
		}
		return false;
	}
}