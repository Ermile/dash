<?php
namespace lib\utility;

class timezone
{

	/**
	 * get current timezone
	 *
	 * @return     <type>  ( description_of_the_return_value )
	 */
	public static function current()
	{
		$timezone = date_default_timezone_get();
		return $timezone;
	}


	/**
	 * get list of timezone
	 */
	public static function list()
	{
		$timezonelist = \DateTimeZone::listIdentifiers();
		return $timezonelist;
	}


	/**
	 * change time from timezone to another timezone
	 *
	 * @param      <type>  $_time               The time
	 * @param      <type>  $_to_timezone        To timezone
	 * @param      <type>  $_current_time_zone  The current time zone
	 */
	public static function change_time($_format, $_time, $_to_timezone, $_current_time_zone = null)
	{
		if(!$_current_time_zone)
		{
			$_current_time_zone = self::current();
		}

		$dt = new \DateTime($_time, new \DateTimeZone($_current_time_zone));
		$dt->setTimezone(new \DateTimeZone($_to_timezone));
		$result = $dt->format($_format);
		return $result;
	}
}
?>