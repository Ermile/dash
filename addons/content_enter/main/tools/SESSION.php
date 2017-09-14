<?php
namespace addons\content_enter\main\tools;
use \lib\utility;
use \lib\debug;

trait SESSION
{
	/**
	* Clean session
	*
	*/
	public static function clean_session()
	{
		$_SESSION['enter'] = [];
		$_SESSION['step']  = [];
		$_SESSION['lock']  = [];
	}


	/**
	 * Sets the enter session.
	 *
	 * @param      <type>  $_key    The key
	 * @param      <type>  $_value  The value
	 */
	public static function set_enter_session($_key, $_value)
	{
		self::set_session('enter', $_key, $_value);
	}


	/**
	 * Gets the enter session.
	 *
	 * @param      <type>  $_key   The key
	 */
	public static function get_enter_session($_key)
	{
		return self::get_session('enter', $_key);
	}


	/**
	 * Sets the step session.
	 *
	 * @param      <type>  $_key    The key
	 * @param      <type>  $_value  The value
	 */
	public static function set_step_session($_key, $_value)
	{
		self::set_session('step', $_key, $_value);
	}


	/**
	 * Gets the enter session.
	 *
	 * @param      <type>  $_key   The key
	 *
	 * @return     <type>  The enter session.
	 */
	public static function get_step_session($_key)
	{
		return self::get_session('step', $_key);
	}


	/**
	 * plus the try session
	 * change mobile
	 * wrong password
	 * ...
	 *
	 *
	 * @param      <type>  $_key   The key
	 */
	public static function plus_try_session($_key)
	{
		$is = self::get_session('try', $_key);
		if($is && is_numeric($is))
		{
			$is++;
		}
		else
		{
			$is = 1;
		}
		self::set_session('try', $_key, $is);
	}


	/**
	 * Gets the session.
	 *
	 * @param      <type>  $_index  The index
	 * @param      <type>  $_key    The key
	 *
	 * @return     <type>  The session.
	 */
	public static function get_session($_index, $_key)
	{
		if(isset($_SESSION[$_index]))
		{
			if(array_key_exists($_key, $_SESSION[$_index]))
			{
				return $_SESSION[$_index][$_key];
			}
		}
		return null;
	}


	/**
	 * Sets the session.
	 *
	 * @param      <type>  $_index  The index
	 * @param      <type>  $_key    The key
	 * @param      <type>  $_value  The value
	 */
	public static function set_session($_index, $_key, $_value)
	{
		if(!isset($_SESSION[$_index]))
		{
			$_SESSION[$_index] = [];
		}
		$_SESSION[$_index][$_key] = $_value;
	}
}
?>