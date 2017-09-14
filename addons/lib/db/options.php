<?php
namespace lib\db;

/** options managing **/
class options
{
	/**
	 * this library work with options table
	 * v1.0
	 */


	/**
	 * insert new recrod in options table
	 * @param array $_args fields data
	 * @return mysql result
	 */
	public static function insert()
	{
		\lib\db\config::public_insert('options', ...func_get_args());
		return \lib\db::insert_id();
	}


	/**
	 * insert multi record in one query
	 *
	 * @param      <type>   $_args  The arguments
	 *
	 * @return     boolean  ( description_of_the_return_value )
	 */
	public static function insert_multi()
	{
		return \lib\db\config::public_insert_multi('options', ...func_get_args());
	}


	/**
	 * update record in options table if we have error in insert
	 * get fields and value to update  WHERE fields = $value
	 * @param array $_args fields data
	 * @return mysql result
	 */
	public static function update_on_error($_args, $_where)
	{
		// ready fields and values to update syntax query [update table set field = 'value' , field = 'value' , .....]
		$set_fields = \lib\db\config::make_set($_args);
		$where      = \lib\db\config::make_where($_where);
		if(!$_args || !$_where)
		{
			return false;
		}

		// make update fields
		$query = "UPDATE options SET $set_fields	WHERE $where ";
		return \lib\db::query($query);
	}


	/**
	 * update field from options table
	 * get fields and value to update
	 * @param array $_args fields data
	 * @param string || int $_id record id
	 * @return mysql result
	 */
	public static function update()
	{
		return \lib\db\config::public_update('options', ...func_get_args());
	}


	/**
	 * we can not delete a record from database
	 * we just update field status to 'deleted' or 'disable' or set this record to black list
	 * @param string || int $_id record id
	 * @return mysql result
	 */
	public static function delete($_where_or_id)
	{

		if(is_numeric($_where_or_id))
		{
			$where = " options.id = $_where_or_id ";
		}
		elseif(is_array($_where_or_id))
		{
			$where = \lib\db\config::make_where($_where_or_id);
		}
		else
		{
			return false;
		}

		$query = " UPDATE options  SET options.status = 'disable' WHERE $where ";
		return \lib\db::query($query);
	}


	/**
	 * real delete record from database
	 *
	 * @param      <type>  $_where_or_id  The where or identifier
	 *
	 * @return     <type>  ( description_of_the_return_value )
	 */
	public static function hard_delete($_where_or_id)
	{
		if(is_numeric($_where_or_id))
		{
			$where = " options.id = $_where_or_id ";
		}
		elseif(is_array($_where_or_id))
		{
			$where = \lib\db\config::make_where($_where_or_id);
		}
		else
		{
			return false;
		}

		$query = " DELETE FROM	options	WHERE $where ";
		return \lib\db::query($query);
	}


	/**
	 * get the record of option table
	 *
	 * @param      <type>   $_args  The arguments
	 *
	 * @return     boolean  ( description_of_the_return_value )
	 */
	public static function get()
	{
		$result = \lib\db\config::public_get('options', ...func_get_args());
		if(isset($result['meta']) && substr($result['meta'], 0, 1) == '{')
		{
			$result['meta'] = json_decode($result['meta'], true);
		}
		return $result;
	}


	/**
	 * update the option record  value++
	 *
	 * @param      <type>  $_where  The where
	 * @param      string  $_field  The field
	 */
	private static function plus_meta($_where, $_plus = 1, $_type = 'plus')
	{
		if(!is_array($_where))
		{
			return false;
		}

		$args = [];
		foreach ($_where as $key => $value)
		{
			if($value === null)
			{
				$args[] = " options.$key = NULL ";
			}
			elseif(is_string($value))
			{
				$args[] = " options.$key  = '$value' ";
			}
			elseif(is_numeric($value))
			{
				$args[] = " options.$key  = $value ";
			}
		}

		if(empty($args))
		{
			return false;
		}


		$update_meta_query = "IF(options.meta IS NULL OR options.meta = '', $_plus, options.meta + $_plus)";
		if($_type === 'minus')
		{
			$update_meta_query = "IF(options.meta IS NULL OR options.meta = '' OR options.meta = 0, $_plus, options.meta - $_plus)";
		}

		$args = join($args, " , ");

		$query =
		"
			INSERT INTO options
			SET
				$args,
				options.meta   = $_plus,
				options.status = 'enable'
			ON DUPLICATE KEY UPDATE
				$args,
				options.meta   = $update_meta_query,
				options.status = 'enable'

		";
		$result = \lib\db::query($query);
		return $result;
	}


	/**
	 * plus options meta
	 *
	 * @param      <type>  $_where  The where
	 * @param      <type>  $_plus   The plus
	 *
	 * @return     <type>  ( description_of_the_return_value )
	 */
	public static function plus($_where, $_plus = 1)
	{
		return self::plus_meta($_where, $_plus, 'plus');
	}



	/**
	 * minus the option meta
	 *
	 * @param      <type>  $_where  The where
	 * @param      <type>  $_minus  The minus
	 *
	 * @return     <type>  ( description_of_the_return_value )
	 */
	public static function minus($_where, $_minus = 1)
	{
		return self::plus_meta($_where, $_minus, 'minus');
	}
}
?>