<?php
namespace lib;

/**
 * Class for temporary.
 * save temp data
 * and get it
 */
class temp
{
	/**
	 * TEMP ARRAY
	 * @var        array
	 */
	private static $TEMP = [];


	/**
	 * set the temp
	 *
	 * @param      <type>  $_key    The key
	 * @param      <type>  $_value  The value
	 */
	public static function set($_key, $_value)
	{
		self::$TEMP[$_key] = $_value;
	}


	/**
	 * get the temp
	 *
	 * @param      <type>  $_key   The key
	 *
	 * @return     <type>  ( description_of_the_return_value )
	 */
	public static function get($_key = null)
	{
		if(!$_key)
		{
			return self::$TEMP;
		}
		elseif(array_key_exists($_key, self::$TEMP))
		{
			return self::$TEMP[$_key];
		}
		return null;
	}
}
?>