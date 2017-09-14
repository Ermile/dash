<?php
namespace lib\db;

class cache
{
	public static $CACHE = [];
	/**
	 * Makes a key.
	 * the argument of function get
	 * passed to this function
	 * make json encode of the argument
	 * return the md5 of this json
	 *
	 * @param      <type>  $_table  The table
	 * @param      <type>  $_args   The arguments
	 *
	 * @return     <type>  ( description_of_the_return_value )
	 */
	public static function make_key($_table, $_args)
	{
		$key = null;
		if(is_array($_args))
		{
			$key = json_encode($_args, JSON_UNESCAPED_UNICODE);
		}
		elseif(is_string($_args) || is_numeric($_args))
		{
			$key = $_args;
		}
		$key = md5($key);
		return $key;
	}


	/**
	 * check cacheed data by the key or no
	 *
	 * @param      <type>  $_table   The table
	 * @param      <type>  $_args    The arguments
	 * @param      <type>  $_option  The option
	 */
	public static function get_cache($_table, $_args = null, $_time = 60)
	{
		$key = self::make_key($_table, $_args);
		if(isset(self::$CACHE['cache_data'][$_table][$key]['data']) && isset(self::$CACHE['cache_data'][$_table][$key]['time']))
		{
			$time = intval(self::$CACHE['cache_data'][$_table][$key]['time']);
			if((time() - $time) < intval($_time))
			{
				return self::$CACHE['cache_data'][$_table][$key]['data'];
			}
			else
			{
				unset(self::$CACHE['cache_data'][$_table][$key]);
				return false;
			}
		}
		else
		{
			return false;
		}
	}


	/**
	 * Sets the cache get.
	 * save result of get function in the session
	 * by key of md5 of arguments of this function
	 *
	 * @param      <type>  $_table   The table
	 * @param      <type>  $_args    The arguments
	 * @param      <type>  $_result  The result
	 */
	public static function set_cache($_table, $_args, $_result)
	{

		$key = self::make_key($_table, $_args);

		if(!isset(self::$CACHE['cache_data']))
		{
			self::$CACHE['cache_data'] = [];
		}

		if(!isset(self::$CACHE['cache_data'][$_table]))
		{
			self::$CACHE['cache_data'][$_table] = [];
		}

		self::$CACHE['cache_data'][$_table][$key]         = [];
		self::$CACHE['cache_data'][$_table][$key]['data'] = $_result;
		self::$CACHE['cache_data'][$_table][$key]['time'] = time();
	}


	/**
	 * update cache
	 * the query update was loaded
	 * unset the session of this table to not cache again
	 *
	 * @param      <type>  $_table  The table
	 */
	public static function clean($_table = null)
	{

		if($_table)
		{
			unset(self::$CACHE['cache_data'][$_table]);
		}
		else
		{
			unset(self::$CACHE['cache_data']);
		}
	}
}
?>