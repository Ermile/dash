<?php
namespace lib\utility;

/** session: handle session of project **/
class session
{
	/**
	 * this library work with session
	 * v3.1
	 */


	/**
	 * save session in options table
	 * @return [type] [description]
	 */
	public static function save($_userid = true, $_meta = false, $_id = null)
	{
		$session_id = session_id();
		// define session array
		$session =
		[
			'user'  => $_userid,
			'cat'   => 'session',
			'key'   => session_name(),
			'value' => $session_id,
		];
		if($_meta)
		{
			$session['meta'] = $_meta;
		}
		if($_id & is_numeric($_id))
		{
			$session['id'] = $_id;
		}
		// save in options table and if successful return session_id
		if(\lib\utility\option::set($session, true))
		{
			return $session_id;
		}
		// else return false
		return false;
	}


	/**
	 * save session id database only one time
	 * if exist use old one
	 * else insert new one to database
	 * @param  [type]  $_userid [description]
	 * @param  boolean $_meta   [description]
	 * @return [type]           [description]
	 */
	public static function save_once($_userid, $_meta = false, $_like = false)
	{
		if(!$_userid)
		{
			$_userid = null;
			$userStr = 'IS NULL';
		}
		else
		{
			$userStr = '= '.$_userid;
			// `user_id` $_userid AND
		}
		// create key value
		$op_key = session_name();
		// create query string
		$qry = "SELECT *
			FROM options
			WHERE

				`cat` = 'session' AND
				`key` = '$op_key'
		";
		// if we have meta then add it to query
		if($_meta)
		{
			if($_like)
			{
				$_like = "LIKE";
			}
			else
			{
				$_like = '=';
			}
			$qry .= "AND `meta` $_like '$_meta'";
		}
		// run query and get result
		$session_exist = \lib\db::get($qry, null, true);
		$session_id    = null;

		// if record is not exist save session for first time
		if(!isset($session_exist['value']))
		{
			$session_id = self::save($_userid, $_meta);
		}
		// for other time, except first time want to add session
		else
		{
			// get variables from datarow
			$session_id     = $session_exist['value'];
			$id      = $session_exist['id'];
			$user_id = $session_exist['user_id'];
			// if session id is not true return false!
			if(!$session_id)
			{
				return false;
			}
			// restart session to use our session id
			self::restart($session_id);

			// if user_id is not set for this user
			// and this is first time we want to add to database
			// call save to save existing session record
			if($_userid && !$user_id)
			{
				$session_id = self::save($_userid, $_meta, $id);
			}
		}
		// if successfully changed session return session id
		return $session_id;
	}


	/**
	 * restart session with new session id
	 * @param  [type] $_session_id new session id
	 * @return [type]              [description]
	 */
	public static function restart($_session_id)
	{
		// if a session is currently opened, close it
		if (session_id() != '')
		{
			session_write_close();
		}
		// use new id
		session_id($_session_id);
		// start new session
		session_start();
	}


	/**
	 * delete session file with id
	 * @param  [type] $_id [description]
	 * @return [type]      [description]
	 */
	public static function delete($_id)
	{
		$path   = session_save_path();
		$result = [];
		if(is_integer($_id))
		{
			$_id = [$_id];
		}
		if(is_array($_id))
		{
			foreach ($_id as $value)
			{
				$filename = $path. '/sess_'.$value;
				$result[$value] = null;
				if(file_exists($filename))
				{
					$result[$value] = @unlink($filename);
				}
			}
		}
		// return result
		return $result;
	}


	/**
	 * delete session file with given perm name
	 * @param  [type]  $_permName [description]
	 * @param  boolean $_exceptMe [description]
	 * @return [type]             [description]
	 */
	public static function deleteByPerm($_permName)
	{
		$permList     = \lib\utility\option::permList(true);
		$deleteResult = [];

		// if permission exist
		if(isset($permList[$_permName]))
		{
			// find user with this permission
			$perm_id = $permList[$_permName];
			// connect to database
			\lib\db::connect(true);
			$qry =
			"SELECT `options`.value
				FROM users
				INNER JOIN `options` ON `options`.user_id = `users`.id
				WHERE `options`.cat = 'session' AND
					permission = $perm_id;";
			// run query and give result
			$result = @mysqli_query(\lib\db::$link, $qry);
			// fetch all records
			$result = \lib\db::fetch_all($result, 'value');
			if($result)
			{
				$deleteResult = self::delete($result);
				// for each file in delete
				foreach ($deleteResult as $key => $value)
				{
					// if file is deleted
					if($value === true)
					{
						$qry = "DELETE FROM options WHERE cat = 'session' AND value = '$key';";
						@mysqli_query(\lib\db::$link, $qry);
					}
				}
				return $deleteResult;
			}
		}
		return null;
	}
}
?>