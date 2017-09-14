<?php
namespace lib\utility;
/** units managing **/
class units
{
	public static $UNITS        = [];

	/**
	 * get units list
	 *
	 * @return     <type>  ( description_of_the_return_value )
	 */
	public static function unit_list()
	{
		if(empty(self::$UNITS))
		{
			self::$UNITS = \lib\option::config('units');
		}
		return self::$UNITS;
	}


	/**
	 * { function_description }
	 *
	 * @param      <type>  $_id    The identifier
	 */
	public static function get($_id = null, $_get_title = false)
	{
		self::unit_list();

		$id     = null;
		$result = false;
		if($_id)
		{
			if(isset(self::$UNITS[$_id]))
			{
				if($_get_title)
				{
					if(isset(self::$UNITS[$_id]['title']))
					{
						$result =  self::$UNITS[$_id]['title'];
					}
				}
				else
				{
					$result =  self::$UNITS[$_id];
				}
			}
		}
		else
		{
			if($_get_title)
			{
				$result = array_column(self::$UNITS, 'title');
			}
			else
			{
				$result = self::$UNITS;
			}
		}
		return $result;
	}


	/**
	 * Gets the unit identifier.
	 *
	 * @param      <type>   $_unit_title  The unit title
	 *
	 * @return     boolean  The identifier.
	 */
	public static function get_id($_unit_title)
	{
		self::unit_list();
		foreach (self::$UNITS as $key => $value)
		{
			if(isset($value['title']) && $value['title'] == $_unit_title)
			{
				return $key;
			}
		}
		return false;
	}


	/**
	 * get the user unit
	 *
	 * @param      <type>  $_caller  The caller
	 */
	public static function user_unit($_user_id)
	{

		$user_unit = \lib\db\users::get_unit($_user_id);
		$force_unit = \lib\option::config('force_unit');

		if($force_unit && (self::get_id($user_unit) != $force_unit))
		{
			self::set_user_unit($_user_id, self::get($force_unit, true));
			return self::get($force_unit, true);
		}

		if($user_unit)
		{
			return $user_unit;
		}
		return false;
	}


	/**
	 * Sets the user unit.
	 *
	 * @param      <type>  $_user_id  The user identifier
	 * @param      <type>  $_unit     The unit
	 *
	 * @return     <type>  ( description_of_the_return_value )
	 */
	public static function set_user_unit($_user_id, $_unit)
	{
		if(!$_unit)
		{
			return false;
		}

		\lib\db\users::set_unit($_user_id, $_unit);
		return true;
	}


	/**
	 * find user unit
	 *
	 * @param      <type>  $_user_id  The user identifier
	 */
	public static function find_user_unit($_user_id, $_set_user_unit_if_find = false)
	{
		// get curretn unit
		$isset_unit = self::user_unit($_user_id);
		if($isset_unit)
		{
			return $isset_unit;
		}

		if($_set_user_unit_if_find)
		{
			if(\lib\option::config('force_unit') && ($force_unit = self::get(\lib\option::config('force_unit'), true)))
			{
				self::set_user_unit($_user_id, $force_unit);
				return $force_unit;
			}
			else
			{
				if(\lib\option::config('default_unit') && ($default_unit = self::get(\lib\option::config('default_unit'), true)))
				{
					self::set_user_unit($_user_id, $default_unit);
					return $default_unit;
				}
			}
		}
		return null;
	}
}
?>