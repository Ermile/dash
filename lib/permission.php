<?php
namespace lib;

/** Access: handle permissions **/
class permission
{

	public static $perm_list       = [];
	public static $user_id         = null;
	public static $caller          = null;
	public static $permission = null;
	public static $force_load_user = false;

	/**
	 * load permission
	 */
	public static function _construct()
	{
		if(empty(self::$perm_list))
		{
			if(file_exists('../permission.php'))
			{
				require_once('../permission.php');
			}
		}

		if(!self::$user_id && isset($_SESSION['user']['id']) && is_numeric($_SESSION['user']['id']))
		{
			self::$user_id = $_SESSION['user']['id'];
		}

		// set permission as static value if exist, but dont need
		self::load_user_data();
	}


	/**
	 * Loads an user data.
	 */
	public static function load_user_data()
	{
		// if permission is set before it, return true
		if(self::$permission)
		{
			return true;
		}
		// if permission is exist in session use it
		if(isset($_SESSION['user']['permission']) && !self::$force_load_user)
		{
			self::$permission = $_SESSION['user']['permission'];
		}
		// else if we have user_id get it from user detail
		else if(self::$user_id && is_numeric(self::$user_id))
		{
			$user_data = \lib\db\users::get_by_id(self::$user_id);
			if(isset($user_data['permission']))
			{
				self::$permission = trim($user_data['permission']);
				// $_SESSION['user']['permission'] = self::$permission;
			}
		}
	}

	/**
	 * check access users
	 *
	 * @param      <type>  $_caller  The caller
	 *
	 * @return     <type>  ( description_of_the_return_value )
	 */
	public static function access($_caller, $_action = null, $_user_id = null)
	{
		// set the user id if user id send to this function and self::user_id not set
		if($_user_id && is_numeric($_user_id) && !self::$user_id)
		{
			self::$user_id = $_user_id;
			self::$force_load_user = true;
		}

		// load permission list and check session if self::$user_id not set
		self::_construct();

		// check permission
		$permission_check = self::check($_caller);

		if($_action === 'notify')
		{
			if($permission_check)
			{
				return true;
			}
			else
			{
				\lib\debug::error(T_("Can not access to it"));
				return false;
			}
		}
		elseif($_action === 'block')
		{
			if($permission_check)
			{
				return true;
			}
			else
			{
				\lib\error::access(T_("Access denied"));
				return false;
			}
		}
		else
		{
			return $permission_check;
		}
	}


	/**
	 * { function_description }
	 *
	 * @param      <type>  $_caller  The caller
	 */
	private static function check($_caller)
	{
		// the user not found!
		if(!self::$user_id)
		{
			return false;
		}
		// no permissin need in this project
		if(empty(self::$perm_list))
		{
			return true;
		}

		self::caller($_caller);

		$user_data_loaded = false;
		if(isset(self::$caller['need_check']))
		{
			self::load_user_data();
			$user_data_loaded = true;
		}

		if(isset(self::$caller['need_verify']))
		{
			if(!$user_data_loaded)
			{
				self::load_user_data();
			}
			// and verify users !
		}
		// admin use -f!
		if(self::$permission === 'admin')
		{
			return true;
		}

		// if permission is not null and exist, explode it
		if(self::$permission && is_string(self::$permission))
		{
			$explode = explode(',', self::$permission);

			if(isset(self::$caller['key']))
			{
				if(in_array(self::$caller['key'], $explode))
				{
					return true;
				}
			}
		}
		return false;
	}


	/**
	 * { function_description }
	 *
	 * @param      <type>  $_caller  The caller
	 */
	private static function caller($_caller)
	{
		$caller              = array_column(self::$perm_list, 'caller');
		$caller              = array_combine(array_keys(self::$perm_list), $caller);
		$key                 = array_search($_caller, $caller);
		self::$caller        = isset(self::$perm_list[$key]) ? self::$perm_list[$key] : null;
		self::$caller['key'] = $key;
	}


	/**
	 * return the perm list
	 */
	public static function list($_group = null)
	{
		return self::$perm_list;
	}
}
?>