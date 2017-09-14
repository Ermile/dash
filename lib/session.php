<?php
namespace lib;
/**
 * Class for session.
 * save data in session and get it
 */
class session
{

	/**
	 * save data in session
	 * by key and cat
	 *
	 * @param      <type>  $_key    The key
	 * @param      <type>  $_value  The value
	 * @param      <type>  $_cat    The cat
	 */
	public static function set($_key, $_value, $_cat = null)
	{
		if($_cat)
		{
			$_SESSION['session_storage'][$_cat][$_key] = $_value;
		}
		else
		{
			$_SESSION['session_storage'][$_key] = $_value;
		}
	}


	/**
	 * get data from session
	 * by check key and cat
	 *
	 * @param      <type>  $_key   The key
	 *
	 * @return     <type>  ( description_of_the_return_value )
	 */
	public static function get($_key = null, $_cat = null)
	{
		if($_key)
		{
			if($_cat)
			{
				if(isset($_SESSION['session_storage'][$_cat][$_key]))
				{
					return $_SESSION['session_storage'][$_cat][$_key];
				}
				else
				{
					return null;
				}
			}
			else
			{
				if(isset($_SESSION['session_storage'][$_key]))
				{
					return $_SESSION['session_storage'][$_key];
				}
				else
				{
					return null;
				}
			}
		}
		else
		{
			if(!$_cat)
			{
				return $_SESSION['session_storage'];
			}
			else
			{
				if(isset($_SESSION['session_storage'][$_cat]))
				{
					return $_SESSION['session_storage'][$_cat];
				}
				else
				{
					return null;
				}
			}
		}
		return null;
	}
}
?>