<?php
namespace lib\db;


class config
{
	/**
	 * get multi insert id
	 *
	 * @param      <type>  $_args  The arguments
	 *
	 * @return     array   ( description_of_the_return_value )
	 */
	public static function multi_insert_id($_args)
	{
		$first = \lib\db::insert_id();
		$count = count($_args);
		$ids = [];
		for ($i = $first; $i <= ($first + $count) - 1 ; $i++)
		{
			$ids[] = $i;
		}
		return $ids;
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
			elseif(is_string($value) && preg_match("/\%/", $value))
			{
				$where[] = " $my_field LIKE '$value' ";
			}
			elseif($value === null || is_null($value))
			{
				$where[] = " $my_field IS NULL ";
			}
			elseif(is_string($value) && substr($value, 0,7) === '(SELECT')
			{
				$where[] = " $my_field = $value ";
			}
			elseif(is_string($value))
			{
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
				$set[] = " `$key` = NULL ";
			}
			elseif(is_numeric($value))
			{
				$set[] = " `$key` = $value ";
			}
			elseif(is_string($value) && substr($value, 0,7) === '(SELECT')
			{
				$set[] = " `$key` = $value ";
			}
			elseif(is_string($value) && (!$value || $value == '' ))
			{
				$set[] = " `$key` = NULL";
			}
			elseif(is_bool($value))
			{
				if($value)
				{
					$set[] = " `$key` = 1";
				}
				else
				{
					$set[] = " `$key` = NULL";
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
					if(is_numeric($value[$field_name]))
					{
						$values[] = $value[$field_name];
					}
					elseif($value[$field_name] === null || (is_string($value[$field_name]) && (!$value[$field_name] || $value[$field_name] === '' )))
					{
						$values[] = "NULL";
					}
					else
					{
						$values[] = "'" . $value[$field_name] . "'";
					}
				}
				else
				{
					$values[] = "NULL";
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
				'table_name'        => null,
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

			unset($_where['limit']);

			$where = \lib\db\config::make_where($_where, $_options);
			if($where)
			{
				$query = "SELECT $_options[public_show_field] FROM $_table $_options[master_join] WHERE $where $limit";
				$result = \lib\db::get($query, null, $only_one_value);
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
	public static function public_multi_insert($_table, $_args)
	{
		$set = \lib\db\config::make_multi_insert($_args);
		if($set)
		{
			$query = " INSERT INTO $_table $set ";
			return \lib\db::query($query);
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
	public static function public_insert($_table, $_args)
	{
		$set = \lib\db\config::make_set($_args);
		if($set)
		{
			$query = " INSERT INTO $_table SET $set ";
			return \lib\db::query($query);
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
	public static function public_update($_table, $_args, $_id)
	{
		$set = \lib\db\config::make_set($_args);
		if($set && $_id && is_numeric($_id))
		{
			// make update query
			$query = "UPDATE $_table SET $set WHERE $_table.id = $_id ";
			return \lib\db::query($query);
		}
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
			// just return the count record
			"get_count"           => false,
			// enable|disable paignation,
			"pagenation"          => true,
			// for example in get_count mode we needless to limit and pagenation
			// default limit of record is 15
			// set the limit      = null and pagenation = false to get all record whitout limit
			"limit"               => 15,
			// for manual pagenation set the statrt_limit and end limit
			"start_limit"         => 0,
			// for manual pagenation set the statrt_limit and end limit
			"end_limit"           => 10,
			// the the last record inserted to post table
			"get_last"            => false,
			// default order by DESC you can change to DESC
			"order"               => "DESC",
			// custom sort by field
			"sort"                => null,
			"search_field"        => null,
			"public_show_field" => null,
			"master_join"         => null,
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

		unset($_options['pagenation']);
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

		$where = join($where, " AND ");
		$search = null;
		if($_string != null && $search_field)
		{
			$_string = trim($_string);

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
			$pagenation_query = "SELECT	COUNT(*) AS `count`	FROM `$_table` $master_join	$where $search ";
			$pagenation_query = \lib\db::get($pagenation_query, 'count', true);

			list($limit_start, $limit) = \lib\db::pagnation((int) $pagenation_query, $limit);
			$limit = " LIMIT $limit_start, $limit ";
		}
		else
		{
			// in get count mode the $limit is null
			if($limit)
			{
				$limit = " LIMIT $start_limit, $end_limit ";
			}
		}

		$json = json_encode(func_get_args());
		if($no_limit)
		{
			$limit = null;
		}

		$query = " SELECT $public_fields $where $search $order $limit -- $_table::search() 	-- $json";

		if(!$only_one_value)
		{
			$result = \lib\db::get($query, null, false);
			$result = \lib\utility\filter::meta_decode($result);
		}
		else
		{
			$result = \lib\db::get($query, 'searchcount', true);
		}

		return $result;
	}
}
?>