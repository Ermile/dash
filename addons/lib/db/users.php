<?php
namespace lib\db;

/** users account managing **/
class users
{
	/**
	 * this library work with acoount
	 * v1.2
	 */
	public static $USERS_DETAIL = [];

	public static $user_id;

	/**
	 * get users data in users table
	 *
	 * @param      <type>  $_user_id  The user identifier
	 */
	public static function get()
	{
		return \lib\db\config::public_get('users', ...func_get_args());
	}


	/**
	 * Gets the by identifier.
	 *
	 * @param      <type>  $_user_id  The user identifier
	 *
	 * @return     <type>  The by identifier.
	 */
	public static function get_by_id($_user_id)
	{
		$args =
		[
			'id'    => $_user_id,
			'limit' => 1
		];
		return self::get($args);
	}


	/**
	 * Searches for the first match.
	 *
	 * @return     <type>  ( description_of_the_return_value )
	 */
	public static function search($_string = null, $_options = [])
	{
		if(!is_array($_options))
		{
			$_options = [];
		}
		$_options['search_field'] =
		"
			(
				users.mobile LIKE '%__string__%' OR
				users.displayname LIKE '%__string__%'
			)
		";
		// public_show_field
		return \lib\db\config::public_search('users', ...func_get_args());
	}


	/**
	 * get all data by email
	 *
	 * @param      <type>  $_email  The email
	 *
	 * @return     <type>  The identifier.
	 */
	public static function get_by_email($_email, $_field = false)
	{
		switch ($_field)
		{
			case 'all':
				$query =
				"
					SELECT * FROM users
					WHERE
					(
						users.email         = '$_email' OR
						users.googlemail   = '$_email' OR
						users.facebookmail = '$_email' OR
						users.twittermail  = '$_email'
					) AND
					users.status != 'removed'
					ORDER BY users.id DESC
					LIMIT 1
				";
				break;

			case 'googlemail':
			case 'facebookmail':
			case 'twittermail':
				$query =
				"
					SELECT *
					FROM users
					WHERE users.$_field = '$_email'
					AND users.status != 'removed'
					ORDER BY users.id DESC
					LIMIT 1
				";
				break;

			case false:
			case 'email':
			default:
				$query =
				"
					SELECT *
					FROM users
					WHERE users.email = '$_email'
					AND users.status != 'removed'
					ORDER BY users.id DESC
					LIMIT 1
				";
				break;
		}

		return \lib\db::get($query, null, true);
	}


	/**
	 * get all data by username
	 *
	 * @param      <type>  $_username  The username
	 *
	 * @return     <type>  The identifier.
	 */
	public static function get_by_username($_username)
	{
		$args =
		[
			'username' => $_username,
			'limit'         => 1
		];
		return self::get($args);
	}


	/**
	 * get all data by mobile
	 *
	 * @param      <type>  $_mobile  The mobile
	 *
	 * @return     <type>  The identifier.
	 */
	public static function get_by_mobile($_mobile)
	{
		$args =
		[
			'mobile' => $_mobile,
			'limit'       => 1
		];
		$result = self::get($args);
		return $result;
	}


	/**
	 * insert new recrod in users table
	 * @param array $_args fields data
	 * @return mysql result
	 */
	public static function insert()
	{
		return \lib\db\config::public_insert('users', ...func_get_args());
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
		\lib\db\config::public_insert_multi('users', ...func_get_args());
		return \lib\db::insert_id();
	}


	/**
	 * update field from users table
	 * get fields and value to update
	 * @param array $_args fields data
	 * @param string || int $_id record id
	 * @return mysql result
	 */
	public static function update()
	{
		return \lib\db\config::public_update('users', ...func_get_args());
	}


	/**
	 * check valid ref
	 *
	 * @param      <type>  $_ref   The reference
	 */
	private static function check_ref($_ref)
	{
		if(!is_string($_ref))
		{
			return null;
		}

		if($_ref)
		{
			$ref_id = \lib\utility\shortURL::decode($_ref);
			if($ref_id)
			{
				$check_ref = self::get($ref_id);
				if(!empty($check_ref))
				{
					return $ref_id;
				}
			}
		}
		return null;
	}


	/**
	 * singup quice user
	 *
	 * @param      array   $_args  The arguments
	 *
	 * @return     <type>  ( description_of_the_return_value )
	 */
	public static function signup_quick($_args = [])
	{
		$_args      = array_merge(['datecreated' => date('Y-m-d H:i:s')], $_args);
		$insert_new = self::insert($_args);
		$insert_id  = \lib\db::insert_id();
		return $insert_id;
	}


	/**
	 * check signup and if can add new user
	 * @return [type] [description]
	 */
	public static function signup($_args = [])
	{
		$default_args =
		[
			'mobile'      => null,
			'password'    => null,
			'email'       => null,
			'permission'  => null,
			'displayname' => null,
			'ref'         => null,
			'type'        => null,
		];

		if(!is_array($_args))
		{
			$_args = [];
		}

		$_args = array_merge($default_args, $_args);

		if($_args['type'] === 'inspection')
		{
			$_args['displayname'] = "Guest Session";
			if(!$_args['mobile'])
			{
				$_args['mobile'] = \lib\utility\filter::temp_mobile();
			}
			$_args['password'] = null;
		}

		// first if perm is true get default permission from db
		if($_args['permission'] === true)
		{
			// if use true fill it with default value
			$_args['permission']     = \lib\option::config('default_permission');
			// default value not set in database
			if($_args['permission'] == '')
			{
				$_args['permission'] = null;
			}
		}
		else
		{
			$_args['permission'] = null;
		}

		$query = " SELECT id FROM users WHERE mobile = '$_args[mobile]' LIMIT 1 ";

		$result = \lib\db::get($query, 'id', true);

		if($result)
		{
			// signup called and the mobile exist
			return false;
		}
		else
		{
			$ref = null;
			// get the ref and set in users_parent
			if(isset($_SESSION['ref']))
			{
				$ref = self::check_ref($_SESSION['ref']);
				if(!$ref)
				{
					$_args['invalid_ref_session'] = $_SESSION['ref'];
				}
			}
			elseif($_args['ref'])
			{
				$ref = self::check_ref($_args['ref']);
				if(!$ref)
				{
					$_args['invalid_ref_args'] = $_args['ref'];
				}
			}
			// elseif(\lib\utility::cookie('ref'))
			// {
			// 	$ref = self::check_ref(\lib\utility::cookie('ref'));
			// }

			if($ref)
			{
				unset($_SESSION['ref']);
			}

			if($_args['password'])
			{
				$password = \lib\utility::hasher($_args['password']);
			}
			else
			{
				$password = null;
			}

			if(!$_args['mobile'])
			{
				return false;
			}

			$_args['displayname'] = \lib\utility\safe::safe($_args['displayname']);

			if(mb_strlen($_args['displayname']) > 99)
			{
				$_args['displayname'] = null;
			}

			// check email exist
			if($_args['email'])
			{
				if(self::get_by_email($_args['email']))
				{
					// the user by this email exist
					return false;
				}
			}
			// signup up users
			$args =
			[
				'mobile'      => $_args['mobile'],
				'password'        => $password,
				'displayname' => $_args['displayname'],
				'permission'  => $_args['permission'],
				'email'       => $_args['email'],
				'parent'      => $ref,
				'datecreated'  => date('Y-m-d H:i:s')
			];

			$insert_new    = self::insert($args);
			$insert_id     = \lib\db::insert_id();
			self::$user_id = $insert_id;

			if(method_exists('\lib\utility\users', 'signup'))
			{
				$_args['insert_id'] = $insert_id;
				$_args['ref']       = $ref;
				\lib\utility\users::signup($_args);
			}
			return $insert_id;
		}
	}



	/**
	 * set login session
	 *
	 * @param      <type>  $_user_id  The user identifier
	 */
	public static function set_login_session($_datarow = null, $_fields = null, $_user_id = null)
	{
		// if user id set load user data by get from database
		if($_user_id)
		{
			// load all user field
			$user_data = self::get_user_data($_user_id);

			// check the reault is true
			if(is_array($user_data))
			{
				$_datarow = $user_data;
			}
			else
			{
				return false;
			}
		}

		// set main cat of session
		$_SESSION['user']       = [];
		$_SESSION['permission'] = [];

		if(is_array($_datarow))
		{
			// and set the session
			foreach ($_datarow as $key => $value)
			{
				if(substr($key, 0, 5) === 'user_')
				{
					// remove 'user_' from first of index of session
					$key = substr($key, 5);
					if($key == 'meta' && is_string($value))
					{
						$_SESSION['user'][$key] = json_decode($value, true);
					}
					else
					{
						$_SESSION['user'][$key] = $value;
					}
				}
				else
				{
					$_SESSION['user'][$key] = $value;
				}
			}
		}
	}


	/**
	 * Gets the count of users
	 * set $_type null to get all users by status and validstatus
	 *
	 * @param      <type>  $_type  The type
	 *
	 * @return     <type>  The count.
	 */
	public static function get_count($_type = null)
	{
		$query = null;
		$field = 'count';
		$only_one_record = true;
		switch ($_type)
		{
			case 'active':
			case 'awaiting':
			case 'deactive':
			case 'removed':
			case 'filter':
				$query = "SELECT COUNT(*) AS 'count' FROM users WHERE users.status = '$_type' ";
				break;

			case 'valid':
			case 'invalid':
				$query = "SELECT COUNT(*) AS 'count' FROM users WHERE users.user_validstatus = '$_type' ";
				break;

			case 'all':
				$query = "SELECT COUNT(*) AS 'count' FROM users";
				break;


			default:
				$query = "SELECT
							users.user_validstatus AS 'valid',
							users.status AS 'status',
							COUNT(*) AS 'count'
						FROM users
						GROUP BY valid,status";
				$field = null;
				$only_one_record = false;
				break;
		}
		$result = \lib\db::get($query, $field, $only_one_record);
		return $result;
	}


		/**
	 * get users method
	 *
	 * @param      <type>  $_fuck  The fuck
	 * @param      <type>  $_args  The arguments
	 */
	public static function __callStatic($_fn, $_args)
	{
		if(preg_match("/^(is|get|set)\_?(.*)$/", $_fn, $split))
		{
			if(isset($split[1]))
			{
				if(isset($_args[0]) && is_numeric($_args[0]))
				{
					if(!isset(self::$USERS_DETAIL[$_args[0]]))
					{
						self::$USERS_DETAIL[$_args[0]] = \lib\db\users::get_by_id($_args[0]);
					}
				}
				if($split[1] === 'get')
				{
					return self::static_get($split[2], ...$_args);
				}

				if($split[1] === 'set')
				{
					return self::static_set($split[2], ...$_args);
				}

				if($split[1] === 'is')
				{
					return self::static_is($split[2], ...$_args);
				}
			}
		}
	}


	/**
	 * get users data
	 *
	 * @param      <type>  $_field    The field
	 * @param      <type>  $_user_id  The user identifier
	 */
	private static function static_get($_field, $_user_id)
	{
		if($_field)
		{
			switch ($_field)
			{
				case null:
					if(isset(self::$USERS_DETAIL[$_user_id]))
					{
						return self::$USERS_DETAIL[$_user_id];
					}
					else
					{
						return false;
					}
					break;

				case 'language':
					if(isset(self::$USERS_DETAIL[$_user_id]['language']))
					{
						return self::$USERS_DETAIL[$_user_id]['language'];
					}
					else
					{
						return false;
					}
					break;

				case 'unit':
					if(isset(self::$USERS_DETAIL[$_user_id]['unit_id']))
					{
						$unit = \lib\utility\units::get(self::$USERS_DETAIL[$_user_id]['unit_id']);
						if(isset($unit['title']))
						{
							return $unit['title'];
						}
					}
					return null;
					break;

				default:
					if(isset(self::$USERS_DETAIL[$_user_id][$_field]))
					{
						return self::$USERS_DETAIL[$_user_id][$_field];
					}
					else
					{
						return null;
					}
					break;
			}
		}
		else
		{
			if(isset(self::$USERS_DETAIL[$_user_id]))
			{
				return self::$USERS_DETAIL[$_user_id];
			}
			else
			{
				return null;
			}
		}
	}


	/**
	 * set users data
	 *
	 * @param      <type>  $_field    The field
	 * @param      <type>  $_user_id  The user identifier
	 */
	private static function static_set($_field, $_user_id, $_value = null)
	{
		$update = [];
		switch ($_field)
		{
			case 'language':
				if(\lib\utility\location\languages::check($_value))
				{
					$update['language'] = $_value;
				}
				break;

			case 'unit':
				$unit_id = \lib\utility\units::get_id($_value);
				if($unit_id)
				{
					$update['unit_id'] = $unit_id;
				}
				break;

			case 'unit_id':
				$check = \lib\utility\units::get($_value);
				if($check)
				{
					$update['unit_id'] = $_value;
				}
				break;

			default:
				$update[$_field] = $_value;
				break;
		}
		if(!empty($update))
		{
			\lib\db\users::update($update, $_user_id);
			unset(self::$USERS_DETAIL[$_user_id]);
		}
	}


	/**
	 * check some field by some value and return true or false
	 * @example self::is_guest(user_id) = false
	 *
	 * @param      <type>   $_field    The field
	 * @param      <type>   $_user_id  The user identifier
	 *
	 * @return     boolean  ( description_of_the_return_value )
	 */
	private static function static_is($_field, $_user_id)
	{
		switch ($_field)
		{
			default:
			if(isset(self::$USERS_DETAIL[$_user_id][$_field]) && self::$USERS_DETAIL[$_user_id][$_field])
			{
				return true;
			}
			else
			{
				return false;
			}
			break;
		}
	}
}
?>