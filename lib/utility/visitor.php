<?php
namespace lib\utility;

/** Visitor: handle visitor details **/
class visitor
{
	/**
	 * this library get visitor detail and do some work on it
	 * v1.2
	 */

	// declare private static variable to save options
	private static $visitor;
	private static $link;
	private static $result;
	private static $external;



	/**
	 * save a visitor in database
	 * @return [type] [description]
	 */
	public static function save()
	{
		// create link to database
		$connect = self::createLink();
		if($connect)
		{
			// create a query string
			$qry     = self::create_query();
			// execute query and save result
			$result  = @mysqli_query(self::$link, $qry);
			// return resul
			return $result;
		}
		// else we have problem in connection, fix it later
		// header("HTTP/1.1 200 OK");
		return $connect;
	}


	/**
	 * create link to database if not exist
	 * @param  boolean $_force [description]
	 * @return [type]          [description]
	 */
	private static function createLink($_force = false)
	{

		if(!self::$link || $_force)
		{
			// open database connection and create link
			if(!\lib\db::connect('[tools]', false))
			{
				// cant connect to database
				return false;
			}
			// save link as global variable
			self::$link = \lib\db::$link;
			return true;
		}
		return true;
	}


	/**
	 * create final query string to add new record to visitors table
	 * @return [string] contain insert query string
	 */
	public static function create_query($_array = false)
	{
		// declare variables
		self::$visitor['`visitor_ip`']       = ClientIP;
		self::$visitor['`service_id`']       = self::checkDetailExist('service', self::url(true));
		self::$visitor['`url_id`']           = self::checkDetailExist('url',     self::url());
		self::$visitor['`agent_id`']         = self::checkDetailExist('agent',   self::agent());
		self::$visitor['`url_idreferer`']    = self::checkDetailExist('url',     self::referer());
		self::$visitor['`user_id`']          = self::user_id();
		self::$visitor['`visitor_external`'] = self::$external;
		self::$visitor['`visitor_date`']     = "'".date('Y-m-d')."'";
		self::$visitor['`visitor_time`']     = "'".date('H:i:s')."'";

		if($_array === true)
		{
			return self::$visitor;
		}

		// create query string
		$qry_fields = implode(', ', array_keys(self::$visitor));
		$qry_values = implode(', ', self::$visitor);
		$qry = "INSERT INTO visitors ( $qry_fields ) VALUES ( $qry_values );";
		// return query
		return $qry;
	}


	/**
	 * get visitor data
	 *
	 * @param      <type>  $_type  The type
	 *
	 * @return     <type>  ( description_of_the_return_value )
	 */
	public static function get($_type = null)
	{
		switch ($_type)
		{
			case 'agent':
				$return = self::checkDetailExist('agent',   self::agent());
				break;

			default:
				$return = self::create_query(true);
				break;
		}
		return $return;
	}


	/**
	 * check value exist in table if not add new one
	 * @param  [type] $_table name of table
	 * @param  [type] $_value value to check
	 * @return [type]         final id
	 */
	public static function checkDetailExist($_table, $_value)
	{
		// create link to database
		self::createLink();
		$default = 0;
		$field   = $_table. '_';
		if($_table === 'service')
		{
			$field .= 'name';
		}
		else
		{
			$field .=  $_table;
		}
		$qry     = "SELECT * FROM $_table"."s WHERE $field = '$_value';";
		// run qry and save result
		$result  = @mysqli_query(self::$link, $qry);
		// if result is not mysqli result return false
		if(!is_a($result, 'mysqli_result'))
		{
			// no record exist
			return 'NULL';
		}
		// if has result return id
		if($result && $row = @mysqli_fetch_assoc($result))
		{
			if(isset($row['id']))
			{
				return $row['id'];
			}
			return $default;
		}

		// create insert query to add new record
		$qry     = "INSERT INTO $_table"."s ( $_table".'_'."$_table ) VALUES ( '$_value' );";
		if($_table === 'agent')
		{
			// self::agent()
			$is_bot = self::isBot();
			$agent  = \lib\utility\browserDetection::browser_detection('full_assoc');
			$qry    =
			"INSERT INTO agents
			(
				`agent_agent`,
				`agent_group`,
				`agent_name`,
				`agent_version`,
				`agent_os`,
				`agent_osnum`,
				`agent_meta`,
				`agent_robot`
			)
			VALUES
			(
				'$_value',
				'".$agent['browser_working']."',
				'".$agent['browser_name']."',
				'".$agent['browser_number']."',
				'".$agent['os']."',
				'".$agent['os_number']."',
				'".json_encode($agent, true)."',
				$is_bot
			);";
		}
		elseif($_table === 'url')
		{
			$qry =
			"INSERT INTO urls
			( url_url, `url_host` )
			VALUES ( '$_value', '". parse_url(urldecode($_value), PHP_URL_HOST). "' );";
		}
		elseif($_table === 'service')
		{
			$qry = "INSERT INTO services ( service_name ) VALUES ( '$_value' );";
		}
		// execute query
		$result  = @mysqli_query(self::$link, $qry);
		// give last insert id
		$last_id = @mysqli_insert_id(self::$link);
		// if have last insert it return it
		if($last_id)
		{
			return $last_id;
		}
		// return default value
		return $default;
	}


	/**
	 * return current url
	 * @return [type] [description]
	 */
	public static function url($_host = false)
	{
		$url = null;
		// get protocol
		$url = 'http'.(empty($_SERVER['HTTPS'])?'':'s').'://';
		// get name
		$url .= $_SERVER['SERVER_NAME'];
		// get port
		$url .= $_SERVER["SERVER_PORT"] != "80"? ":".$_SERVER["SERVER_PORT"]: '';
		// get request url
		$url .= $_SERVER['REQUEST_URI'];
		// if user want only host
		if($_host)
		{
			$url = self::domain($url);
		}
		$url = urlencode($url);
		// return result
		return $url;
	}


	/**
	 * return current service id
	 * @return [type] [description]
	 */
	public static function service()
	{
		$domain = self::url(true);
		$qry     = "SELECT * FROM services WHERE service_name = '$domain';";
		// run qry and save result
		$result  = @mysqli_query(self::$link, $qry);
		// if has result return id
		if(is_a($result, 'mysqli_result') && $row = @mysqli_fetch_assoc($result))
		{
			if(isset($row['id']))
			{
				return $row['id'];
			}
		}
		return 'NULL';
	}


	/**
	 * get url and return the name of domain
	 * @param  [type] $_url [description]
	 * @return [type]       [description]
	 */
	public static function domain($_url)
	{
		$pieces = parse_url($_url);
		$domain = isset($pieces['host']) ? $pieces['host'] : '';
		if (preg_match('/(?P<domain>[a-z0-9][a-z0-9\-]{1,63}\.[a-z\.]{2,6})$/i', $domain, $regs))
		{
			return $regs['domain'];
		}
		return false;
	}


	/**
	 * return user_id if loginned to system
	 * @return [type] [description]
	 */
	public static function user_id($_returnNull = true)
	{
		$userid = null;
		if($_returnNull)
		{
			$userid = 'NULL';
		}
		if(isset($_SESSION['user']['id']))
		{
			$userid = $_SESSION['user']['id'];
		}
		// return result
		return $userid;
	}


	/**
	 * return referer of visitor in current page
	 * @return [type] [description]
	 */
	public static function referer($_encode = true)
	{
		$referer = null;
		if(isset($_SERVER['HTTP_REFERER']))
		{
			$referer = $_SERVER['HTTP_REFERER'];
		}
		$host_referer   = parse_url(urldecode($referer), PHP_URL_HOST);
		if($host_referer === $_SERVER['SERVER_NAME'])
		{
			self::$external = 0;
		}
		else
		{
			self::$external = 1;
		}

		// if user want encode referer
		if($_encode)
		{
			$referer = urlencode($referer);
		}

		return $referer;
	}


	/**
	 * return agent of visitor in current page
	 * @return [type] [description]
	 */
	public static function agent($_encode = true)
	{
		$agent = null;
		if(isset($_SERVER['HTTP_USER_AGENT']))
		{
			$agent = $_SERVER['HTTP_USER_AGENT'];
		}
		// if user want encode referer
		if($_encode)
		{
			$agent = urlencode($agent);
		}
		return $agent;
	}


	/**
	 * check current user is bot or not
	 * @return boolean [description]
	 */
	public static function isBot()
	{
		$robot   = 'NULL';
		$agent   = self::agent();
		$botlist =
		[
			"Teoma",
			"alexa",
			"froogle",
			"Gigabot",
			"inktomi",
			"looksmart",
			"URL_Spider_SQL",
			"Firefly",
			"NationalDirectory",
			"Ask Jeeves",
			"TECNOSEEK",
			"InfoSeek",
			"WebFindBot",
			"girafabot",
			"crawler",
			"www.galaxy.com",
			"Googlebot",
			"Scooter",
			"Slurp",
			"msnbot",
			"appie",
			"FAST",
			"WebBug",
			"Spade",
			"ZyBorg",
			"rabaz",
			"Baiduspider",
			"Feedfetcher-Google",
			"TechnoratiSnoop",
			"Rankivabot",
			"Mediapartners-Google",
			"Sogou web spider",
			"WebAlta Crawler",
			"TweetmemeBot",
			"Butterfly",
			"Twitturls",
			"Me.dium",
			"Twiceler",
			"inoreader",
			"yoozBot",
		];

		foreach($botlist as $bot)
		{
			if(strpos($agent, $bot) !== false)
			{
				$robot = true;
			}
		}
		// return result
		return $robot;
	}


	/**
	 * Install visitor databases
	 * @return [type] [description]
	 */
	public static function install()
	{
		return \lib\db::execFolder('(core_name)_tools', 'visitor', true);
	}


	/**
	 * show visitor result
	 * @return [type] [description]
	 */
	public static function chart()
	{
		self::createLink();
		$service_id = self::service();
		/**
		 add getting unique visitor in next update
		 */

		$qry =
			"SELECT
				visitor_date as date,
				0 as bots,
				count(*) as humans,
				count(*) as total

				FROM `visitors`
				WHERE `service_id` = $service_id

				GROUP BY visitor_date
				ORDER BY visitor_date DESC
				LIMIT 0, 10";
		$result  = @mysqli_query(self::$link, $qry);
		if(!$result)
		{
			return false;
		}

		$result = \lib\db::fetch_all($result);
		$result = array_reverse($result);

		$result_total = array_column($result, 'total');
		self::$result['chart'] = $result;
		self::$result['total'] = null;
		self::$result['max']   = null;
		self::$result['min']   = null;
		if($result_total)
		{
			self::$result['total'] = array_sum($result_total);
			self::$result['max']   = max($result_total);
			self::$result['min']   = min($result_total);
		}
		// return result
		return $result;
	}


	/**
	 * return top pages visited on this site
	 * @return [type] [description]
	 */
	public static function top_pages($_count = 10)
	{
		self::createLink();
		$service_id = self::service();

		$qry =
			"SELECT
				urls.url_url as url,
				count(visitors.id) as total
			FROM urls
			INNER JOIN visitors ON urls.id = visitors.url_id
			WHERE visitors.`service_id` = $service_id

			GROUP BY visitors.url_id
			ORDER BY total DESC
			LIMIT 0, $_count";
		$result  = @mysqli_query(self::$link, $qry);
		if(!$result)
			return false;

		$result = \lib\db::fetch_all($result);

		foreach ($result as $key => $row)
		{
			$result[$key]['url'] = urldecode($row['url']);
			if(strpos($result[$key]['url'], 'http://') !== false)
			{
				$result[$key]['text'] = substr($result[$key]['url'], 7);
			}

		}
		return $result;
	}
}
?>