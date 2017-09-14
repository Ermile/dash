<?php
namespace lib\db;

/** logitems managing **/
class logitems
{
	/**
	 * this library work with logitems table
	 * v1.0
	 */

	/**
	 * Gets the database name.
	 * if defined db_log return the db_log name to connect to this database
	 * else return true to connect to default database
	 *
	 * @return     boolean  The database name.
	 */
	public static function get_db_log_name()
	{
		if(defined('db_log_name'))
		{
			return db_log_name;
		}
		else
		{
			return true;
		}
	}

	public static $fields =
	"
			logitems.type            	AS 	`type`,
			logitems.caller          	AS 	`caller`,
			logitems.title           	AS 	`title`,
			logitems.desc            	AS 	`desc`,
			logitems.meta            	AS 	`meta`,
			IFNULL(logitems.count, 0)	AS 	`count`,
			logitems.priority        	AS 	`priority`,
			logitems.datemodified    	AS 	`datemodified`
		FROM
			logitems
	";


	/**
	 * insert new recrod in logitems table
	 * @param array $_args fields data
	 * @return mysql result
	 */
	public static function insert($_args)
	{
		$set = \lib\db\config::make_set($_args);
		if($set)
		{
			$query = " INSERT INTO 	logitems SET $set ";
			return \lib\db::query($query, self::get_db_log_name());
		}
	}


	/**
	 * update field from logitems table
	 * get fields and value to update
	 * @param array $_args fields data
	 * @param string || int $_id record id
	 * @return mysql result
	 */
	public static function update($_args, $_id)
	{
		$set = \lib\db\config::make_set($_args);
		if($set)
		{
			$query = " UPDATE logitems SET $set	WHERE logitems.id = $_id ";
			return \lib\db::query($query, self::get_db_log_name());
		}
	}



	/**
	 * Gets the logitem id by logitem caller
	 *
	 * @param      <type>  $_caller  The logitem caller
	 *
	 * @return     <type>  The identifier.
	 */
	public static function get_id($_caller)
	{
		$log_item = self::caller($_caller);
		if($log_item && isset($log_item['id']))
		{
			return $log_item['id'];
		}
		elseif(is_numeric($log_item))
		{
			return $log_item;
		}
		return false;
	}


	/**
	 * Gets the logitem record by logitem caller
	 *
	 * @param      <type>  $_caller  The logitem caller
	 *
	 * @return     <type>  The identifier.
	 */
	public static function caller($_caller, $_options = [])
	{
		$default_args =
		[
			'all_field' => false,
		];
		$_options = array_merge($default_args, $_options);

		$field     = 'id';
		$get_field = 'id';

		if($_options['all_field'])
		{
			$field     = '*';
			$get_field = null;
		}

		$query = " SELECT $field FROM logitems 	WHERE logitems.caller = '$_caller' LIMIT 1 ";
		$result = \lib\db::get($query, $get_field, true, self::get_db_log_name());
		if(!$result || empty($result))
		{
			return self::auto_insert($_caller);
		}
		return $result;
	}


	/**
	 * auto insert record of logitems
	 *
	 * @param      <type>  $_caller  The caller
	 *
	 * @return     <type>  ( description_of_the_return_value )
	 */
	private static function auto_insert($_caller)
	{
		$insert_log_items =
		[
			'caller'   => $_caller,
			'title'    => $_caller,
		];

		$result = self::insert($insert_log_items);
		if($result)
		{
			return (int) \lib\db::get("SELECT id AS `id` FROM logitems WHERE logitems.caller = '$_caller' LIMIT 1 ", 'id', true, self::get_db_log_name());
		}
		return false;
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
			"get_count"   => false,
			// enable|disable paignation,
			"pagenation"  => true,
			// for example in get_count mode we needless to limit and pagenation
			// default limit of record is 15
			// set the limit  = null and pagenation = false to get all record whitout limit
			"limit"           => 15,
			// for manual pagenation set the statrt_limit and end limit
			"start_limit"     => 0,
			// for manual pagenation set the statrt_limit and end limit
			"end_limit"       => 10,
			// the the last record inserted to post table
			"get_last"        => false,
			// default order by ASC you can change to DESC
			"order"           => "ASC",
			// custom sort by field
			"sort"			  => null,
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
			$public_fields  = " COUNT(logitems.id) AS 'logcount' FROM logitems ";
			$limit          = null;
			$only_one_value = true;
		}
		else
		{
			$limit         = null;
			$public_fields = self::$fields;
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
				case 'type':
				case 'caller':
				case 'title':
				case 'desc':
				case 'meta':
				case 'priority':
					$temp_sort = ''.  $_options['sort'];
					break;
				case 'count':
				case 'datemodified':
					$temp_sort = $_options['sort'];
					break;
				default:
					$temp_sort = 'id';
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
				$order = " ORDER BY logitems.id DESC ";
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
				$order = " ORDER BY logitems.id $_options[order] ";
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

		foreach ($_options as $key => $value)
		{
			if(is_array($value))
			{
				if(isset($value[0]) && isset($value[1]) && is_string($value[0]) && is_string($value[1]))
				{
					// for similar "logitems.`field` LIKE '%valud%'"
					$where[] = " logitems.`$key` $value[0] $value[1] ";
				}
			}
			elseif($value === null)
			{
				$where[] = " logitems.`$key` IS NULL ";
			}
			elseif(is_numeric($value))
			{
				$where[] = " logitems.`$key` = $value ";
			}
			elseif(is_string($value))
			{
				$where[] = " logitems.`$key` = '$value' ";
			}
		}

		$where = join($where, " AND ");
		$search = null;
		if($_string != null)
		{
			$search =
			"(
				logitems.type 		LIKE '%$_string%' OR
				logitems.caller 	LIKE '%$_string%' OR
				logitems.title 		LIKE '%$_string%'
			)";
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
			$pagenation_query = (int) \lib\db::get("SELECT COUNT(*) AS `count` FROM logitems $where $search -- get count log for pagenation", 'count', true, self::get_db_log_name());
			list($limit_start, $limit) = \lib\db::pagnation($pagenation_query, $limit);
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
		$query = " SELECT $public_fields $where	$search	$order $limit	-- logitems::search()	-- $json";

		if(!$only_one_value)
		{
			$result = \lib\db::get($query, null, false, self::get_db_log_name());
			$result = \lib\utility\filter::meta_decode($result);
		}
		else
		{
			$result = \lib\db::get($query, 'logcount', true, self::get_db_log_name());
		}

		return $result;
	}
}
?>