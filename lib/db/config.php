<?php
namespace dash\db;


class config
{

	public static function ready_to_sql($_and, $_or, $_order_sort = null, $_meta = [])
	{
		$where = null;
		$q     = [];

		if($_and)
		{
			$_and = implode(' AND ', $_and);
			$q[] = "$_and";

		}

		if($_or)
		{
			$_or = implode(' OR ', $_or);
			$q[] = "($_or)";
		}

		if($q)
		{
			$where = 'WHERE '. implode(" AND ", $q);
		}

		$order = null;
		if($_order_sort && is_string($_order_sort))
		{
			$order = $_order_sort;
		}

		$pagination = null;
		if(array_key_exists('pagination', $_meta))
		{
			$pagination = $_meta['pagination'];
		}

		$limit = null;
		if(array_key_exists('limit', $_meta))
		{
			$limit = $_meta['limit'];
		}

		$start_limit = null;
		if(array_key_exists('start_limit', $_meta))
		{
			$start_limit = $_meta['start_limit'];
		}

		if(isset($_meta['join']) && is_array($_meta['join']) && $_meta['join'])
		{
			$join = implode(' ', $_meta['join']);
		}
		else
		{
			$join = null;
		}

		$fields = '*';
		if(isset($_meta['fields']))
		{
			$fields = $_meta['fields'];
		}

		$result =
		[
			'where'       => $where,
			'order'       => $order,
			'pagination'  => $pagination,
			'limit'       => $limit,
			'start_limit' => $start_limit,
			'join'        => $join,
			'fields'      => $fields,
		];

		return $result;
	}

	public static function public_plus_field($_table, $_id, $_field)
	{
		return self::public_change_field('plus', ...func_get_args());
	}


	public static function public_minus_field($_table, $_id, $_field)
	{
		return self::public_change_field('minus', ...func_get_args());
	}


	public static function public_change_field($_type, $_table, $_id, $_field)
	{
		if(!$_type || !$_table || !$_id || !$_field)
		{
			return false;
		}

		if($_type === 'plus')
		{
			$query =
			"
			 	UPDATE `$_table`
			 	SET `$_table`.`$_field` = IF(`$_table`.`$_field` IS NULL OR `$_table`.`$_field` = '', 1, `$_table`.`$_field` + 1)
			 	WHERE `$_table`.`id` = $_id
			 	LIMIT 1
			 ";
		}
		else
		{
			$query =
			"
				UPDATE `$_table`
				SET `$_table`.`$_field` = IF(`$_table`.`$_field` IS NULL OR `$_table`.`$_field` = '' OR `$_table`.`$_field` = 1 OR `$_table`.`$_field` = 0 , 0, `$_table`.`$_field` - 1)
				WHERE `$_table`.`id` = $_id
				LIMIT 1
			";
		}
		return \dash\db::query($query);
	}


	/**
	 * get multi insert id
	 *
	 * @param      <type>  $_args  The arguments
	 *
	 * @return     array   ( description_of_the_return_value )
	 */
	public static function multi_insert_id($_args, $_first_id = null)
	{
		if($_first_id && is_int($_first_id))
		{
			$first = $_first_id;
		}
		else
		{
			$first = \dash\db::insert_id();
		}

		$count = count($_args);
		$ids = [];
		for ($i = $first; $i <= ($first + $count) - 1 ; $i++)
		{
			$ids[] = $i;
		}
		return $ids;
	}


	/**
	 * Gets the count of users
	 * set $_type null to get all users by status and validstatus
	 *
	 * @param      <type>  $_type  The type
	 *
	 * @return     <type>  The count.
	 */
	public static function public_get_count($_table, $_where = null, $_db_name = true)
	{
		if(!$_table || !is_string($_table))
		{
			return false;
		}

		$query           = null;
		$field           = 'count';
		$only_one_record = true;

		if($_where)
		{
			$where = \dash\db\config::make_where($_where);
			if(!$where)
			{
				return false;
			}
			$query = "SELECT COUNT(*) AS 'count' FROM `$_table` WHERE $where ";
		}
		else
		{
			$query = "SELECT COUNT(*) AS 'count' FROM `$_table`";
		}

		if($query)
		{
			$result = \dash\db::get($query, $field, $only_one_record, $_db_name);
			return intval($result);
		}
		return 0;
	}



	/**
	 * Makes a where.
	 *
	 * @param      <type>  $_where  The where
	 *
	 * @return     array   ( description_of_the_return_value )
	 */
	public static function make_where($_where, $_options = [])
	{
		$default_options =
		[
			'condition'  => 'AND',
			'table_name' => null,
		];

		if(!is_array($_options))
		{
			$_options = [];
		}

		$_options = array_merge($default_options, $_options);

		$table_name = null;
		if($_options['table_name'])
		{
			$table_name = "`$_options[table_name]`.";
		}

		if(!is_array($_where))
		{
			$_where = [];
		}

		$where = [];
		foreach ($_where as $field => $value)
		{
			$my_field = "$table_name`$field`";

			if(preg_match("/\./", $field))
			{
				$my_field = "$field";
			}

			if(is_array($value))
			{
				if(isset($value[0]) && isset($value[1]) && is_string($value[0]) && is_string($value[1]))
				{
					$where[] = " $my_field $value[0] $value[1] ";
				}
			}
			elseif($value === null || is_null($value) || $value === '')
			{
				$where[] = " $my_field IS NULL ";
			}
			elseif(is_string($value))
			{
				// if(\dash\db::$link)
				// {
				// 	$value = \mysqli_real_escape_string(\dash\db::$link, $value);
				// }
				$where[] = " $my_field = '$value' ";
			}
			elseif(is_numeric($value))
			{
				$where[] = " $my_field = $value ";
			}
		}

		if(!empty($where))
		{
			$where = implode($_options['condition'], $where);
		}
		else
		{
			$where = false;
		}

		return $where;
	}


	/**
	 * Makes a set.
	 *
	 * @param      <type>  $_args  The arguments
	 */
	public static function make_set($_args, $_options = [])
	{
		$default_options =
		[
			'type' => 'update',
		];

		if(!is_array($_options))
		{
			$_options = [];
		}

		$_options = array_merge($default_options, $_options);

		$set = [];
		foreach ($_args as $key => $value)
		{
			if($value === null)
			{
				if($_options['type'] === 'insert')
				{
					// continue;
				}
				else
				{
					$set[] = " `$key` = NULL ";
				}
			}
			elseif(is_string($value) && (!isset($value) || $value == '' ))
			{
				if($_options['type'] === 'insert')
				{
					// continue;
				}
				else
				{
					$set[] = " `$key` = NULL ";
				}
			}
			elseif(is_string($value))
			{
				// if(\dash\db::$link)
				// {
				// 	$value = \mysqli_real_escape_string(\dash\db::$link, $value);
				// }
				$set[] = " `$key` = '$value' ";
			}
			elseif(is_numeric($value))
			{
				$set[] = " `$key` = $value ";
			}
			elseif(is_bool($value))
			{
				if($value)
				{
					$set[] = " `$key` = 1 ";
				}
				else
				{
					$set[] = " `$key` = NULL ";
				}
			}
			else
			{
				$set[] = " `$key` = '$value' ";
			}
		}

		if(!empty($set))
		{
			if($_options['type'] === 'update')
			{
				$set = implode(',', $set);
			}
			elseif($_options['type'] === 'insert')
			{
				$set = implode(',', $set);
			}
			else
			{
				$set = false;
			}
		}
		else
		{
			$set = false;
		}
		return $set;
	}


	public static function make_multi_insert($_args)
	{
		if(!is_array($_args))
		{
			return false;
		}
		// marge all input array to creat list of field to be insert
		$fields = [];
		foreach ($_args as $key => $value)
		{
			$fields = array_merge($fields, $value);
		}

		// empty record not inserted
		if(empty($fields))
		{
			return false;
		}

		// creat multi insert query : INSERT INTO TABLE (FIELDS) VLUES (values), (values), ...
		$values = [];
		$together = [];
		foreach ($_args	 as $key => $value)
		{
			foreach ($fields as $field_name => $vain)
			{
				if(array_key_exists($field_name, $value))
				{
					if((is_string($value[$field_name]) && !$value[$field_name]) || is_null($value[$field_name]))
					{
						$values[] = " NULL";
					}
					elseif(is_string($value[$field_name]))
					{
						$values[] = " '$value[$field_name]'";
					}
					elseif(is_numeric($value[$field_name]))
					{
						$values[] = " $value[$field_name]";
					}
					elseif(is_bool($value[$field_name]))
					{
						if($value[$field_name])
						{
							$values[] = " 1";
						}
						else
						{
							$values[] = " NULL";
						}
					}
					else
					{
						$values[] = " '$value[$field_name]'";
					}
				}
				else
				{
					$values[] = " NULL";
				}
			}
			$together[] = join($values, ",");
			$values     = [];
		}

		$fields = '`'.  join(array_keys($fields), "`,`"). '`';

		$values = join($together, "),(");

		$temp_query = "($fields) VALUES ($values) ";
		return $temp_query;
	}


	/**
	 * public get from tables
	 *
	 * @param      <type>  $_table  The table
	 * @param      <type>  $_where   The arguments
	 *
	 * @return     <type>  ( description_of_the_return_value )
	 */
	public static function public_get($_table, $_where, $_options = [])
	{
		if($_where && $_table)
		{
			$default_options =
			[
				'public_show_field' => "*",
				'master_join'       => null,
				'order'             => null,
				'table_name'        => null,
				'db_name'           => true,
			];

			if(!is_array($_options))
			{
				$_options = [];
			}
			$_options = array_merge($default_options, $_options);

			$only_one_value = false;
			$limit          = null;

			if(isset($_where['limit']))
			{
				if($_where['limit'] === 1)
				{
					$only_one_value = true;
				}

				$limit = " LIMIT $_where[limit] ";
			}

			if(is_array($_where))
			{
				unset($_where['limit']);
			}

			$where = \dash\db\config::make_where($_where, $_options);
			if($where)
			{
				$query = "SELECT $_options[public_show_field] FROM $_table $_options[master_join] WHERE $where $_options[order] $limit";
				$result = \dash\db::get($query, null, $only_one_value, $_options['db_name']);
				return $result;
			}

		}
		return false;
	}


	/**
	 * make multi insert
	 *
	 * @param      <type>  $_table  The table
	 * @param      <type>  $_args   The arguments
	 */
	public static function public_multi_insert($_table, $_args, $_db_name = true)
	{
		$set = \dash\db\config::make_multi_insert($_args);
		if($set)
		{
			$query = " INSERT INTO $_table $set ";
			return \dash\db::query($query, $_db_name);
		}
	}


	/**
	 * insert public
	 *
	 * @param      <type>  $_table  The table
	 * @param      <type>  $_args   The arguments
	 *
	 * @return     <type>  ( description_of_the_return_value )
	 */
	public static function public_insert($_table, $_args, $_db_name = true)
	{
		$set = \dash\db\config::make_set($_args, ['type' => 'insert']);
		if($set)
		{
			$query = " INSERT INTO $_table SET $set ";
			return \dash\db::query($query, $_db_name);
		}
	}


	/**
	 * update public
	 *
	 * @param      <type>  $_args  The arguments
	 * @param      <type>  $_id    The identifier
	 *
	 * @return     <type>  ( description_of_the_return_value )
	 */
	public static function public_update($_table, $_args, $_id, $_db_name = true)
	{
		$set = \dash\db\config::make_set($_args);
		if($set && $_id && is_numeric($_id))
		{
			// make update query
			$query = "UPDATE $_table SET $set WHERE $_table.id = $_id LIMIT 1";
			return \dash\db::query($query, $_db_name);
		}
	}


	/**
	 * update record by where
	 * not by record id
	 *
	 * @param      <type>  $_table  The table
	 * @param      <type>  $_set    The set
	 * @param      <type>  $_where  The where
	 */
	public static function public_update_where($_table, $_set, $_where)
	{
		$set = self::make_set($_set);
		$where = self::make_where($_where);
		if(!$set || !$where)
		{
			return false;
		}
		$query = "UPDATE $_table SET $set WHERE $where";
		return \dash\db::query($query);
	}


	public static function public_delete_where($_table, $_where)
	{
		$where = self::make_where($_where);

		if(!$where)
		{
			return false;
		}
		$query = "DELETE FROM  $_table WHERE $where";
		return \dash\db::query($query);
	}


	/**
	 * Searches for the first match.
	 *
	 * @param      <type>  $_string   The string
	 * @param      array   $_options  The options
	 */
	public static function public_search($_table, $_string = null, $_options = [])
	{
		$where = []; // conditions

		if(!$_string && empty($_options))
		{
			// default return of this function 10 last record of search
			$_options['get_last'] = true;
		}

		$default_options =
		[
			'join_remove_on_pagination' => false,
			// just return the count record
			"get_count"         => false,
			// enable|disable paignation,
			"pagenation"        => true,
			// for example in get_count mode we needless to limit and pagenation
			// default limit of record is 10
			// set the limit    = null and pagenation = false to get all record whitout limit
			"limit"             => 10,
			// for manual pagenation set the statrt_limit and end limit
			"start_limit"       => 0,
			// for manual pagenation set the statrt_limit and end limit
			"end_limit"         => 10,
			// the the last record inserted to post table
			"get_last"          => false,
			// default order by DESC you can change to DESC
			"order"             => "DESC",
			"order_rand"        => false,
			"order_raw"         => null,
			// custom sort by field
			"sort"              => null,
			"search_field"      => null,
			"public_show_field" => null,
			"master_join"       => null,
			"db_name"           => true,
			"group_by"          => null,
			"sql_having"        => null,
		];

		// if limit not set and the pagenation is false
		// remove limit from query to load add record
		if(!isset($_options['limit']) && array_key_exists('pagenation', $_options) && $_options['pagenation'] === false)
		{
			$default_options['limit'] = null;
			$default_options['end_limit'] = null;
		}

		$_options = array_merge($default_options, $_options);

		$pagenation = false;
		if($_options['pagenation'])
		{
			// page nation
			$pagenation = true;
		}

		$master_join = null;
		if($_options['master_join'])
		{
			$master_join = $_options['master_join'];
		}

		if($_options['order'] && !in_array(mb_strtolower($_options['order']), ['asc', 'desc']))
		{
			$_options['order'] = 'DESC';
		}

		// ------------------ get count
		$only_one_value = false;
		$get_count      = false;

		if($_options['get_count'] === true)
		{
			$get_count      = true;
			$public_fields  = " COUNT(*) AS 'searchcount' FROM	`$_table` $master_join";
			$limit          = null;
			$only_one_value = true;
		}
		else
		{
			$limit         = null;
			if($_options['public_show_field'])
			{
				$public_show_field = $_options['public_show_field'];
			}
			else
			{
				$public_show_field = " * ";
			}

			$public_fields = " $public_show_field FROM `$_table` $master_join";

			if($_options['limit'])
			{
				$limit = $_options['limit'];
			}
		}


		if($_options['sort'])
		{
			$temp_sort = null;
			switch ($_options['sort'])
			{
				default:
					$temp_sort = $_options['sort'];
					break;
			}
			$_options['sort'] = $temp_sort;
		}

		// ------------------ get last
		$order = null;
		if($_options['get_last'])
		{
			if($_options['sort'])
			{
				$order = " ORDER BY $_options[sort] $_options[order] ";
			}
			else
			{
				$order = " ORDER BY `$_table`.`id` DESC ";
			}
		}
		elseif($_options['order_rand'])
		{
			$order = " ORDER BY RAND() ";
		}
		else
		{
			if($_options['sort'])
			{
				if(!preg_match("/\./", $_options['sort']))
				{
					$order = " ORDER BY `$_options[sort]` $_options[order] ";
				}
				else
				{
					$order = " ORDER BY $_options[sort] $_options[order] ";
				}
			}
			else
			{
				$order = " ORDER BY `$_table`.`id` $_options[order] ";
			}
		}

		if(isset($_options['order_raw']) && $_options['order_raw'])
		{
			$order = " ORDER BY ".  $_options['order_raw'];
		}

		$start_limit = $_options['start_limit'];
		$end_limit   = $_options['end_limit'];

		$no_limit = false;
		if($_options['limit'] === false)
		{
			$no_limit = true;
		}

		$search_field = null;
		if($_options['search_field'])
		{
			$search_field = $_options['search_field'];
		}


		$group_by = null;
		if($_options['group_by'])
		{
			$group_by = $_options['group_by'];
		}


		$sql_having = null;
		if($_options['sql_having'])
		{
			$sql_having = $_options['sql_having'];
		}

		$db_name = $_options['db_name'];

		unset($_options['pagenation']);
		unset($_options['sql_having']);
		unset($_options['search_field']);
		unset($_options['get_count']);
		unset($_options['limit']);
		unset($_options['start_limit']);
		unset($_options['end_limit']);
		unset($_options['get_last']);
		unset($_options['order']);
		unset($_options['sort']);
		unset($_options['public_show_field']);
		unset($_options['master_join']);
		unset($_options['order_raw']);
		unset($_options['db_name']);
		unset($_options['group_by']);

		foreach ($_options as $key => $value)
		{
			if(!preg_match("/\./", $key))
			{
				$fkey = " `$key` ";
			}
			else
			{
				$fkey = " $key ";
			}

			if(is_array($value))
			{
				if(isset($value[0]) && isset($value[1]) && is_string($value[0]) && is_string($value[1]))
				{
					// for similar "search.`field` LIKE '%valud%'"
					$where[] = " $fkey $value[0] $value[1] ";
				}
			}
			elseif($value === null)
			{
				$where[] = " $fkey IS NULL ";
			}
			elseif(is_numeric($value))
			{
				$where[] = " $fkey = $value ";
			}
			elseif(is_string($value))
			{
				$where[] = " $fkey = '$value' ";
			}
		}

		$where = implode(' AND ', $where);
		$search = null;
		if($_string && $search_field && !is_array($_string))
		{

			$_string = trim($_string);
			$_string = \dash\safe::forQueryString($_string);
			$search = str_replace('__string__', $_string, $search_field);
			// "($search_field LIKE '%$_string%' )";

			if($where)
			{
				$search = " AND ". $search;
			}
		}

		if($where)
		{
			$where = "WHERE $where";
		}
		elseif($search)
		{
			$where = "WHERE";
		}

		if($pagenation && !$get_count)
		{
			if($sql_having || $group_by)
			{
				$pagenation_query = "SELECT COUNT(*) AS `count` FROM (SELECT $public_fields $where $search $group_by $sql_having $order) AS `myCountTable` ";
			}
			else
			{
				if(isset($_options['join_remove_on_pagination']) && $_options['join_remove_on_pagination'])
				{
					$pagenation_query = "SELECT	COUNT(*) AS `count`	FROM `$_table`	$where $search $group_by";
				}
				else
				{
					$pagenation_query = "SELECT	COUNT(*) AS `count`	FROM `$_table` $master_join	$where $search $group_by";
				}
			}

			$pagenation_query = \dash\db::get($pagenation_query, 'count', true, $db_name);
			list($limit_start, $limit) = \dash\db::pagnation((int) $pagenation_query, $limit);
			$limit = " LIMIT $limit_start, $limit ";
		}
		else
		{
			// in get count mode the $limit is null
			if($limit)
			{
				$limit = " LIMIT $start_limit, $limit ";
			}
		}


		if($no_limit)
		{
			$limit = null;
		}

		$query = "SELECT $public_fields $where $search $group_by $sql_having $order $limit";

		if(!$only_one_value)
		{
			$result = \dash\db::get($query, null, false, $db_name);
			$result = \dash\utility\filter::meta_decode($result);
		}
		else
		{
			$result = \dash\db::get($query, 'searchcount', true);
		}

		return $result;
	}
}
?>
