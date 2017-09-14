<?php
namespace lib\db;

trait info
{
	private static $all_db_version        = [];
	private static $all_db_addons_version = [];

	/**
	 * read query info and analyse it and return array contain result
	 * @return [type] [description]
	 */
	public static function qry_info($_needle = null, $_link = null)
	{
		if($_link === null)
		{
			$_link = self::$link;
		}
		preg_match_all ('/(\S[^:]+): (\d+)/', mysqli_info($_link), $matches);
		$info = array_combine ($matches[1], $matches[2]);
		if($_needle && isset($info[$_needle]))
		{
			$info = $info[$_needle];
		}
		return $info;
	}


	/**
	 * get rows matched
	 *
	 * @return     <type>  ( description_of_the_return_value )
	 */
	public static function rows_matched($_link = null)
	{
		return self::qry_info("Rows matched", $_link);
	}


	/**
	 * get rows changed
	 *
	 * @return     <type>  ( description_of_the_return_value )
	 */
	public static function changed($_link = null)
	{
		return self::qry_info("Changed", $_link);
	}


	/**
	 * get the warnings
	 *
	 * @return     <type>  ( description_of_the_return_value )
	 */
	public static function warnings($_link = null)
	{
		return self::qry_info("Warnings", $_link);
	}


	/**
	 * return the last insert id
	 *
	 * @return     <type>  ( description_of_the_return_value )
	 */
	public static function insert_id($_link = null)
	{
		if($_link === null)
		{
			$_link = self::$link;
		}
		$last_id = @mysqli_insert_id($_link);
		return $last_id;
	}


	/**
	 * return version of mysql used on server
	 * @return [type] [description]
	 */
	public static function version($_link = null)
	{
		if($_link === null)
		{
			$_link = self::$link;
		}
		// mysqli_get_client_info();
		// mysqli_get_client_version();
		return mysqli_get_server_version($_link);
	}


	/**
	 * get num rows of query
	 *
	 * @return     <int>  ( description_of_the_return_value )
	 */
	public static function num($_link = null)
	{
		if($_link === null)
		{
			$_link = self::$link;
		}
		$num = @mysqli_num_rows($_link);
		// $num = self::$link->affected_rows;
		return $num;
	}


	/**
	 * get the affected rows
	 *
	 * @return     <type>  ( description_of_the_return_value )
	 */
	public static function affected_rows($_link = null)
	{
		if($_link === null)
		{
			$_link = self::$link;
		}
		return mysqli_affected_rows($_link);
	}


	/**
	 * return the mysql error
	 */
	public static function error($_link = null)
	{
		if($_link === null)
		{
			$_link = self::$link;
		}
		return @mysqli_error($_link);
	}


	/**
	 * get the database version from options table
	 *
	 * @param      boolean  $_db_name  The database name
	 */
	public static function db_version($_db_name = true, $_addons_version = false)
	{

		self::connect($_db_name, false);

		$db_name = self::$db_name;

		// $core_name = core_name.'_tools';
		// need to fix it
		$core_name = 'saloos_tools';

		if(empty(self::$all_db_addons_version) || empty(self::$all_db_version))
		{
			$query = "SELECT * FROM $core_name.db_version ";

			$db_version = \lib\db::get($query);
			if(empty($db_version) || !$db_version || !is_array($db_version))
			{
				return false;
			}
			else
			{
				foreach ($db_version as $key => $value)
				{
					if(isset($value['addons_version']))
					{
						self::$all_db_addons_version[$value['db_name']] = $value['addons_version'];
					}
					if(isset($value['version']))
					{
						self::$all_db_version[$value['db_name']] = $value['version'];
					}
				}
			}
		}

		if($_addons_version === true)
		{
			if(isset(self::$all_db_addons_version[$db_name]))
			{
				return self::$all_db_addons_version[$db_name];
			}
			else
			{
				return false;
			}
		}
		else
		{
			if(isset(self::$all_db_version[$db_name]))
			{
				return self::$all_db_version[$db_name];
			}
			else
			{
				return false;
			}
		}
	}


	/**
	 * Sets the database version.
	 *
	 * @param      <type>   $_version  The version
	 * @param      boolean  $_db_name  The database name
	 */
	public static function set_db_version($_version, $_db_name = true, $_addons_version = false)
	{
		self::connect($_db_name, false);

		$db_name = self::$db_name;

		// $core_name = core_name.'_tools';
		// need to fi it!
		$core_name = 'saloos_tools';

		if($_addons_version === true)
		{
			$query =
			"
				INSERT INTO
					$core_name.db_version
				SET
					db_version.db_name        = '$db_name',
					db_version.addons_version = '$_version',
					db_version.version        = db_version.version
				ON DUPLICATE KEY UPDATE
					db_version.addons_version = '$_version'
			";
		}
		else
		{
			$query =
			"
				INSERT INTO
					$core_name.db_version
				SET
					db_version.db_name        = '$db_name',
					db_version.version        = '$_version',
					db_version.addons_version = db_version.addons_version
				ON DUPLICATE KEY UPDATE
					db_version.version = '$_version'
			";
		}

		\lib\db::query($query);
	}


	/**
	 * check version of db and custom version
	 *
	 * @param      <type>   $_condition  The condition
	 * @param      <type>   $_version    The version
	 * @param      boolean  $_db         The database
	 *
	 * @return     boolean  ( description_of_the_return_value )
	 */
	public static function check_version($_condition, $_version, $_db = true)
	{
		$version = '0.0.0';

		if($_db === true)
		{
			$version = self::db_version();
		}
		else
		{
			$version = self::db_version(true, true);
		}

		return version_compare($version, $_version, $_condition);
	}
}
?>