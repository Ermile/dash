<?php
namespace lib\db;

/** locations managing **/
class locations
{

	/**
	 * insert new recrod in locations table
	 * @param array $_args fields data
	 * @return mysql result
	 */
	public static function insert($_args)
	{
		$set   = \lib\db\config::make_set($_args);
		$query ="INSERT IGNORE INTO	locations SET $set ";
		return \lib\db::query($query, '[tools]');
	}


	/**
	 * update field from locations table
	 * get fields and value to update
	 * @param array $_args fields data
	 * @param string || int $_id record id
	 * @return mysql result
	 */
	public static function update($_args, $_id)
	{
		$set   = \lib\db\config::make_set($_args);
		$query = " UPDATE locations SET $set WHERE locations.id = $_id ";
		return \lib\db::query($query, '[tools]');
	}



	/**
	 * get log
	 *
	 * @param      <type>   $_args  The arguments
	 *
	 * @return     boolean  ( description_of_the_return_value )
	 */
	public static function get($_args)
	{
		$only_one_recort = false;

		if(empty($_args) || !is_array($_args))
		{
			return false;
		}

		if(isset($_args['limit']))
		{
			if($_args['limit'] == 1)
			{
				$only_one_recort = true;
			}

			$limit = "LIMIT ". $_args['limit'];
			unset($_args['limit']);
		}
		else
		{
			$limit = null;
		}

		$where = [];
		foreach ($_args as $key => $value)
		{
			if(preg_match("/\%/", $value))
			{
				$where[] = " locations.$key LIKE '$value'";
			}
			elseif($value === null)
			{
				$where[] = " locations.$key IS NULL";
			}
			elseif(is_numeric($value))
			{
				$where[] = " locations.$key = $value ";
			}
			elseif(is_string($value))
			{
				$where[] = " locations.$key = '$value'";
			}
		}
		$where = "WHERE ". join($where, " AND ");

		$query = " SELECT * FROM locations $where $limit ";

		$result = \lib\db::get($query, null, $only_one_recort, '[tools]');
		if(isset($result['meta']) && substr($result['meta'], 0, 1) == '{')
		{
			$result['meta'] = json_decode($result['meta'], true);
		}
		else
		{
			$result = \lib\utility\filter::meta_decode($result);
		}
		return $result;
	}



	/**
	 * Searches for the first match.
	 *
	 * @param      <type>  $_string   The string
	 * @param      array   $_options  The options
	 */
	public static function search($_string = null, $_options = [])
	{
		$where = []; // conditions

		if(!$_string && empty($_options))
		{
			// default return of this function 10 last record of poll
			$_options['get_last'] = true;
		}

		$default_options =
		[
			// just return the count record
			"get_count"      => false,
			// enable|disable paignation,
			"pagenation"     => true,
			// for example in get_count mode we needless to limit and pagenation
			// default limit of record is 15
			// set the limit = null and pagenation = false to get all record whitout limit
			"limit"          => 15,
			// for manual pagenation set the statrt_limit and end limit
			"start_limit"    => 0,
			// for manual pagenation set the statrt_limit and end limit
			"end_limit"      => 10,
			// the the last record inserted to post table
			"get_last"       => false,
			// default order by DESC you can change to DESC
			"order"          => "DESC",
			// custom sort by field
			"sort"           => null,
			// search in caller
			"caller"         => null,
		];

		$_options = array_merge($default_options, $_options);

		$pagenation = false;
		if($_options['pagenation'])
		{
			// page nation
			$pagenation = true;
		}

		// ------------------ get count
		$only_one_value = false;
		$get_count      = false;

		if($_options['get_count'] === true)
		{
			$get_count      = true;
			$public_fields  = " COUNT(*) AS 'locationcount' FROM locations ";
			$limit          = null;
			$only_one_value = true;
		}
		else
		{
			$limit         = null;
			$public_fields = '* FROM locations';

			if($_options['limit'])
			{
				$limit = $_options['limit'];
			}
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
				$order = " ORDER BY locations.id DESC ";
			}
		}
		else
		{
			if($_options['sort'])
			{
				$order = " ORDER BY $_options[sort] $_options[order] ";
			}
			else
			{
				$order = " ORDER BY locations.id $_options[order] ";
			}
		}

		$start_limit = $_options['start_limit'];
		$end_limit   = $_options['end_limit'];

		unset($_options['pagenation']);
		unset($_options['get_count']);
		unset($_options['limit']);
		unset($_options['start_limit']);
		unset($_options['end_limit']);
		unset($_options['get_last']);
		unset($_options['order']);
		unset($_options['sort']);
		unset($_options['caller']);

		foreach ($_options as $key => $value)
		{
			if(is_array($value))
			{
				if(isset($value[0]) && isset($value[1]) && is_string($value[0]) && is_string($value[1]))
				{
					// for similar "locations.`field` LIKE '%valud%'"
					$where[] = " locations.`$key` $value[0] $value[1] ";
				}
			}
			elseif($value === null)
			{
				$where[] = " locations.`$key` IS NULL ";
			}
			elseif(is_numeric($value))
			{
				$where[] = " locations.`$key` = $value ";
			}
			elseif(is_string($value))
			{
				$where[] = " locations.`$key` = '$value' ";
			}
		}

		$where = join($where, " AND ");
		$search = null;
		if($_string != null)
		{
			$search = "(locations.search LIKE '%$_string%')";
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
			$pagenation_query = "SELECT	COUNT(locations.id) AS `count` FROM locations $where $search ";
			$pagenation_query = \lib\db::get($pagenation_query, 'count', true, '[tools]');

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
		$query = " SELECT $public_fields $where $search $order $limit -- locations::search() 	-- $json";

		if(!$only_one_value)
		{
			$result = \lib\db::get($query, null, false, '[tools]');
			$result = \lib\utility\filter::meta_decode($result);
		}
		else
		{
			$result = \lib\db::get($query, 'locationcount', true, '[tools]');
		}

		return $result;
	}


	public static function __callStatic($_func, $_args)
	{
		if(preg_match("/^get_child$/", $_func))
		{
			return self::static_child(...$_args);
		}
		if(preg_match("/^(get|get_child)\_?(.*)$/", $_func, $split))
		{
			if(isset($split[1]))
			{
				if($split[1] === 'get')
				{
					return self::static_get($split[2], ...$_args);
				}
			}
		}

	}


	/**
	 * get static
	 *
	 * @return     boolean  ( description_of_the_return_value )
	 */
	private static function static_get()
	{
		$args = func_get_args();
		$type = isset($args[0]) ? $args[0] : null;
		$id   = isset($args[1]) ? $args[1] : null;

		if(!$type || !$id)
		{
			return false;
		}
		$query = "SELECT * FROM locations WHERE `type` = '$type' AND  `id` = $id LIMIT 1 ";
		$result = \lib\db::get($query, null, true, '[tools]');

		return $result;

	}


	/**
	 * get static
	 *
	 * @return     boolean  ( description_of_the_return_value )
	 */
	private static function static_child()
	{
		$args = func_get_args();
		$id   = isset($args[0]) ? $args[0] : null;
		if(!$id)
		{
			return false;
		}
		$query  = "SELECT * FROM locations WHERE `parent` = $id ";
		$result = \lib\db::get($query, null, false, '[tools]');
		return $result;

	}
}
?>