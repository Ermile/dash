<?php
namespace addons\content_enter\main\tools;
use \lib\utility;
use \lib\debug;
use \lib\db;
/**
 * FUNCTION LIST:
 *
 * create_guest($_authorization)
 * create_tmp_login($_authorization, $_guest_token = false)
 * verify($_token, $_user_id)
 * get_api_key($_user_id)
 * create_api_key($_user_id)
 * destroy_api_key($_user_id)
 * destroy($_token)
 *
 * private create_token()
 * private check()
 * private get()
 */
class token
{
	/**
	 * the api key or token
	 *
	 * @var        <type>
	 */
	private static $API_KEY = null;


	/**
	 * the parent api key
	 *
	 * @var        <type>
	 */
	public static $PARENT  = null;


	/**
	 * Creates a guest.
	 */
	public static function create_guest($_authorization)
	{
		$parent = self::check($_authorization, 'api_key');
		return self::create_token(['parent' => $parent, 'type' => 'guest']);
	}


	/**
	 * Creates a temporary login.
	 */
	public static function create_tmp_login($_authorization, $_guest_token = null)
	{
		$parent = self::check($_authorization, 'api_key');
		return self::create_token(['parent' => $parent, 'type' => 'tmp_login', 'guest_token' => $_guest_token]);
	}


	/**
	 * create
	 *
	 * @param      <type>   $_authorization  The authorization
	 * @param      boolean  $_type          The guest
	 *
	 * @return     string   ( description_of_the_return_value )
	 */
	private static function create_token($_args = [])
	{
		if(!debug::$status)
		{
			return null;
		}

		$default_args =
		[
			'parent'      => null,
			'type'        => null,
			'user_id'     => 0,
			'guest_token' => null,
			'save_to_db'  => true,
		];

		$_args = array_merge($default_args, $_args);

		self::$API_KEY = null;
		$user_id       = null;
		$key           = 'undefined'; // to fix db error: Column key can not be null;

		if($_args['type'] == 'guest')
		{
			$user_id = db\users::signup(['type' => 'inspection', 'port' => 'api_guest']);
			$key     = 'guest';
		}
		elseif($_args['type'] == 'tmp_login')
		{
			$user_id = null;
			$key     = 'tmp_login';
		}
		elseif($_args['type'] == 'user_token')
		{
			$user_id = $_args['user_id'];
			$key     = 'user_token';
		}

		$date  = date("Y-m-d H:i:s");
		$token = "~ERMILE~_!_". $user_id . $key. time(). rand(1,1000). $date;
		$token = utility::hasher($token, null, true);

		$token = utility\safe::safe($token);

		if($_args['save_to_db'])
		{

			$meta  = [];
			$meta['time'] = $date;

			$guest_id = null;


			if($_args['guest_token'])
			{
				$guest_token_type = self::get_type($_args['guest_token']);
				if($guest_token_type == 'guest')
				{
					$user_id  = self::get_user_id($_args['guest_token']);
					$guest_id = self::get_id($_args['guest_token']);
				}
			}

			$meta['guest'] = $guest_id;

			$args  =
			[
				'user_id'      => $user_id,
				'parent_id'    => $_args['parent'],
				'cat'   => 'token',
				'key'   => $key,
				'value' => $token,
				'meta'  => json_encode($meta, JSON_UNESCAPED_UNICODE),
			];

			db\options::insert($args);
		}

		return $token;
	}


	/**
	 * check temp token is verified or no
	 *
	 * @param      <type>  $_temp_token  The temporary token
	 */
	public static function check_verify($_temp_token)
	{
		$where =
		[
			'value'  => $_temp_token,
			'status' => 'enable',
			'key'    => 'tmp_login',
			'cat'    => 'token',
			'limit'         => 1
		];
		$result = db\options::get($where);

		if(isset($result['meta']) && $result['meta'] == 'verified')
		{
			if(isset($result['user_id']) && $result['user_id'] && isset($result['parent_id']) && $result['parent_id'])
			{
				$arg =
				[
					'parent'  => $result['parent_id'],
					'user_id' => $result['user_id'],
					'type'    => 'user_token',
				];

				unset($where['limit']);
				db\options::update_on_error(['status' => 'disable'], $where);
				return self::create_token($arg);
			}
			else
			{
				debug::error(T_("Invalid user or parent"), 'user', 'system');
			}
		}
		else
		{
			debug::error(T_("Token not verified"),'temp_token', 'argument');
		}
		return null;
	}


	/**
	 * check authorization
	 *
	 * @param      <type>   $_authorization  The authorization
	 *
	 * @return     boolean  ( description_of_the_return_value )
	 */
	public static function check($_authorization, $_type = 'token')
	{
		$api_key_parent = null;

		$where =
		[
			'value'  => $_authorization,
			'cat'    => 'token',
			'status' => 'enable',
			'limit'         => 1
		];

		$get = db\options::get($where);

		if(!$get || empty($get) || !array_key_exists('parent_id', $get))
		{
			debug::error(T_("authorization faild (parent not found)"), 'authorization', 'access');
			return false;
		}

		$parent_id = $get['parent_id'];

		switch ($_type)
		{
			case 'user_token':
			case 'guest':
				if(is_null($parent_id))
				{
					debug::error(T_("authorization faild (this authorization is not a valid token)"), 'authorization', 'access');
					return false;
				}
				break;

			case 'token':
			case 'temp_token':

				$temp_time = self::get_time($_authorization);

				$max_life_time = 60 * 7; // 7 min

				if(!$temp_time ||  \DateTime::createFromFormat('Y-m-d H:i:s', $temp_time) === false)
				{

					debug::error(T_("Invalid token"), 'authorization', 'system');
					return false;
				}

				$now          = time();
				$temp_time   = strtotime($temp_time);
				$diff_seconds = $now - $temp_time;

				if($diff_seconds > $max_life_time)
				{
					return debug::error(T_("The api key is expired"), 'authorization', 'access');
				}

				break;

			case 'api_key':
				if(!is_null($parent_id))
				{
					debug::error(T_("authorization faild (this authorization is not a api key)"), 'authorization', 'access');
					return false;
				}
				break;

			default:
				debug::error(T_("Invalid type"), 'authorization', 'system');
				return false;
				break;
		}

		if(isset($get['id']))
		{
			$api_key_parent = $get['id'];
		}

		return $api_key_parent;
	}


	/**
	 * verify $_tmp_login
	 *
	 * @param      <type>  $_token  The temporary login token
	 * @param      <type>  $_guest_token      The guest token
	 */
	public static function verify($_token, $_user_id)
	{
		self::check($_token, 'token');

		if(!debug::$status)
		{
			return;
		}

		self::$API_KEY = null;

		$type = self::get_type($_token);
		if($type == 'tmp_login')
		{
			$max_life_time = 60 * 7; // 7 min
			$token_time = self::get_time($_token);

			if(!$token_time ||  \DateTime::createFromFormat('Y-m-d H:i:s', $token_time) === false)
			{
				debug::error(T_("Invalid token"), 'authorization', 'system');
				return false;
			}

			$now          = time();
			$token_time   = strtotime($token_time);
			$diff_seconds = $now - $token_time;

			if($diff_seconds > $max_life_time)
			{
				debug::error(T_("Invalid token"), 'authorization', 'time');
				return false;
			}

			$guest_token_id      = self::get_meta_guest($_token);
			$guest_token_user_id = self::get_user_id($_token);

			if($guest_token_user_id && $guest_token_user_id != $_user_id)
			{
				debug::error(T_("Invalid token"), 'authorization', 'user');
				return false;
			}

			$user_token  = null;

			if($guest_token_user_id && $guest_token_id)
			{
				$where = ['id' => $guest_token_id];
				$arg   = ['status' => 'disable'];
				db\options::update_on_error($arg, $where);
			}

			$where = ['value' => $_token, 'status' => 'enable'];
			$arg =
			[
				'user_id'      => $_user_id,
				'key'   => 'tmp_login',
				'meta'  => 'verified'
			];
			db\options::update_on_error($arg, $where);
			return self::$PARENT;
			// return true;

		}
		else
		{
			debug::error(T_("Invalid token (tmp login)"), 'authorization', 'access');
			return false;
		}

	}


	/**
	 * get token from db
	 *
	 * @param      <type>  $_authorization  The authorization
	 *
	 * @return     <type>  ( description_of_the_return_value )
	 */
	private static function get($_authorization)
	{
		if(!self::$API_KEY)
		{
			$arg =
			[
				'cat'    => 'token',
				'status' => 'enable',
				'value'  => $_authorization,
				'limit'         => 1
			];
			$tmp = db\options::get($arg);
			self::$API_KEY = $tmp;
		}

		return self::$API_KEY;
	}


	/**
	 * get_type
	 * get_parent
	 * get_value
	 *
	 * @param      <type>  $_name  The name
	 * @param      <type>  $_arg   The argument
	 */
	public static function __callStatic($_name, $_authorization)
	{
		if(preg_match("/^(get)\_(.*)$/", $_name, $field))
		{
			self::get(...$_authorization);
			if(isset($field[2]))
			{
				$field = $field[2];
			}
			else
			{
				$field = null;
			}

			if($field == 'time')
			{
				$field = 'meta_time';
			}

			if(preg_match("/^(meta)\_(.*)$/", $field, $meta))
			{
				if(isset($meta[2]))
				{
					$meta = $meta[2];
					if(isset(self::$API_KEY['meta'][$meta]))
					{
						return self::$API_KEY['meta'][$meta];
					}
					else
					{
						return null;
					}
				}
				else
				{
					return null;
				}
			}

			// type of authorization is key of options table
			if($field == 'type')
			{
				$field = 'key';
			}

			if(isset(self::$API_KEY['parent_id']))
			{
				$arg =
				[
					'id'            => self::$API_KEY['parent_id'],
					'status' => 'enable',
					'limit'         => 1
				];
				self::$PARENT = db\options::get($arg);
			}

			if(isset(self::$API_KEY[$field]))
			{
				return self::$API_KEY[$field];
			}
		}
	}



	/**
	 * get token data to show
	 */
	public static function get_api_key($_user_id)
	{
		$where =
		[
			'user_id'       => $_user_id,
			'cat'    => 'token',
			'key'    => 'api_key',
			'status' => 'enable',
			'limit'         => 1
		];
		$api_key = db\options::get($where);

		if($api_key && isset($api_key['value']))
		{
			return $api_key['value'];
		}
	}


	/**
	 * Creates an api key.
	 *
	 * @param      string  $_user_id  The user identifier
	 *
	 * @return     string  ( description_of_the_return_value )
	 */
	public static function create_api_key($_user_id)
	{
		self::destroy_api_key($_user_id);

		$api_key = "!~ERMILE~!". $_user_id. ':_$_:'. time(). "*Ermile*". rand(2, 200);
		$api_key = utility::hasher($api_key, null, true);
		$api_key = utility\safe::safe($api_key);
		$arg =
		[
			'user_id'      => $_user_id,
			'cat'   => 'token',
			'key'   => 'api_key',
			'value' => $api_key
		];
		$set = db\options::insert($arg);
		if($set)
		{
			return $api_key;
		}
	}


	/**
	 * destroy api keuy
	 *
	 * @param      <type>  $_user_id  The user identifier
	 */
	public static function destroy_api_key($_user_id)
	{
		$where =
		[
			'user_id'    => $_user_id,
			'cat' => 'token',
			'key' => 'api_key'
		];
		$set = ['status' => 'disable'];
		db\options::update_on_error($set, $where);
	}


	/**
	 * destroy token when log out
	 *
	 * @param      <type>  $_token  The token
	 */
	public static function destroy($_token)
	{
		$where =
		[
			'cat'   => 'token',
			'key'   => 'user_token',
			'value' => $_token,
		];
		$set = ['status' => 'disable'];
		return db\options::update_on_error($set, $where);
	}
}
?>