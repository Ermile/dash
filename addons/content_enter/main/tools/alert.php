<?php
namespace addons\content_enter\main\tools;

trait alert
{
	/**
	 * Opens a alert.
	 *
	 * @param      <type>  $_module  The module
	 */
	public static function get_alert($_need = 'alert')
	{
		return self::get_session('alert', $_need);
	}


	/**
	 * disable all alert page and set only this module is true
	 *
	 * @param      <type>  $_module  The module
	 */
	public static function set_alert($_msg)
	{
		self::set_session('alert', 'alert', $_msg);
	}


	/**
	 * Sets the alert link.
	 *
	 * @param      <type>  $_link  The link
	 */
	public static function set_alert_link($_link)
	{
		self::set_session('alert', 'link', $_link);
	}


	/**
	 * Sets the alert button.
	 *
	 * @param      <type>  $_button  The button
	 */
	public static function set_alert_button($_button)
	{
		self::set_session('alert', 'button', $_button);
	}
}
?>