<?php
namespace lib\db;

/** passwords managing **/
class passwords
{
	/**
	 * this library work with passwords table
	 * v1.0
	 */

	/**
	 * inset new password in database
	 *
	 * @param      <type>  $_password  The password
	 *
	 * @return     <type>  ( description_of_the_return_value )
	 */
	public static function set($_password, $_status = 'normal', $_creat_date = false)
	{
		if($_creat_date === null)
		{
			$_creat_date = " `createdate` = NULL ";
		}
		else
		{
			$_creat_date = " `createdate` = '". date("Y-m-d H:i:s") ."' ";
		}

		$query = "INSERT IGNORE INTO passwords SET `password` = '$_password', `status` = '$_status', $_creat_date ";
		return \lib\db::query($query, '[tools]');
	}


	/**
	 * get the password details
	 *
	 * @param      <type>  $_password  The password
	 *
	 * @return     <type>  ( description_of_the_return_value )
	 */
	public static function get($_password)
	{
		$query = "SELECT * FROM  passwords	WHERE `password` = '$_password' LIMIT 1";
		return \lib\db::get($query, null, true, '[tools]');
	}


	/**
	 * check the password status and return true if the password is normal
	 *
	 * @param      <type>   $_password  The password
	 *
	 * @return     boolean  ( description_of_the_return_value )
	 */
	public static function check($_password)
	{
		self::set($_password);
		$password = self::get($_password);
		if(isset($password['status']) && $password['status'] == 'normal')
		{
			return true;
		}
		else
		{
			return isset($password['status']) ? $password['status'] : 'crazy';
		}
	}


	/**
	 * plus the field of passwords
	 *
	 * @param      <type>  $_password  The password
	 * @param      <type>  $_field     The field
	 *
	 * @return     <type>  ( description_of_the_return_value )
	 */
	private static function plus($_password, $_field)
	{
		$query = "UPDATE passwords SET `$_field` = `$_field` + 1 WHERE `password` = '$_password' LIMIT 1";
		return \lib\db::query($query, '[tools]');
	}


	/**
	 * save password and check status
	 * if status is true plus the `try` field
	 * if status is false plus the `wrong` field
	 * if status is string plus the `used` feld
	 *
	 * @param      <type>   $_password  The password
	 * @param      boolean  $_status    The status
	 */
	public static function cash($_password, $_status)
	{
		self::set($_password);
		if($_status === true)
		{
			self::plus($_password, 'try');
		}
		elseif($_status === false)
		{
			self::plus($_password, 'wrong');
		}
		elseif(is_string($_status))
		{
			self::plus($_password, 'used');
		}
	}
}
?>