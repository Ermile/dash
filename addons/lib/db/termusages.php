<?php
namespace lib\db;

/** termusage managing **/
class termusages
{
	/**
	 * this library work with termusages
	 * v1.0
	 */


	/**
	 * insert new tag in termusages table
	 * @param array $_args fields data
	 * @return mysql result
	 */
	public static function insert()
	{
		return \lib\db\config::public_insert('termusages', ...func_get_args());
	}


	/**
	 * get termusage
	 *
	 * @return     <type>  ( description_of_the_return_value )
	 */
	public static function get()
	{
		return \lib\db\config::public_get('termusages', ...func_get_args());
	}


	/**
	 * hard delete crod of teamusage from database
	 *
	 * @param      <type>  $_where  The where
	 *
	 * @return     <type>  ( description_of_the_return_value )
	 */
	public static function hard_delete($_where)
	{
		$where = \lib\db\config::make_where($_where);
		if($where)
		{
			$query = "DELETE FROM termusages WHERE $where LIMIT 1";
			return \lib\db::query($query);
		}
	}


	/**
	 * set status of termusage as deleted
	 *
	 * @param      <type>  $_where  The where
	 *
	 * @return     <type>  ( description_of_the_return_value )
	 */
	public static function delete($_where)
	{
		$set = ['status' => 'deleted'];
		return self::update($_where, $set);
	}


	/**
	 * { function_description }
	 *
	 * @param      <type>   $_where   The old
	 * @param      <type>   $_set   The new
	 *
	 * @return     boolean  ( description_of_the_return_value )
	 */
	public static function update($_where, $_set)
	{
		$set = \lib\db\config::make_set($_set);
		$where = \lib\db\config::make_where($_where);

		$query = " UPDATE termusages SET $set WHERE $where LIMIT 1 ";
		return \lib\db::query($query);
	}


	/**
	 * insert mutli tags (get id of tags) to teruseage
	 *
	 * @param      <type>  $_args  The arguments
	 *
	 * @return     <type>  ( description_of_the_return_value )
	 */
	public static function insert_multi($_args, $_options = [])
	{
		if(empty($_args))
		{
			return false;
		}

		$default_options = ['ignore' => false];

		if(!is_array($_options))
		{
			$_options = [];
		}

		$_options = array_merge($default_options, $_options);

		// marge all input array to creat list of field to be insert
		$fields = [];
		foreach ($_args as $key => $value)
		{
			$fields = array_merge($fields, $value);
		}

		// creat multi insert query : INSERT INTO TABLE (FIELDS) VLUES (values), (values), ...
		$values   = [];
		$together = [];
		foreach ($_args	 as $key => $value)
		{
			foreach ($fields as $field_name => $vain)
			{
				if(array_key_exists($field_name, $value))
				{
					$values[] = "'" . $value[$field_name] . "'";
				}
				else
				{
					$values[] = "NULL";
				}
			}
			$together[] = join($values, ",");
			$values = [];
		}

		if(empty($fields))
		{
			return null;
		}

		$fields = join(array_keys($fields), ",");

		$values = join($together, "),(");

		$ignore = null;
		if($_options['ignore'])
		{
			$ignore = "IGNORE";
		}
		// crate string query
		$query = "INSERT $ignore INTO termusages ($fields) VALUES ($values) ";
		return \lib\db::query($query);
	}

}
?>