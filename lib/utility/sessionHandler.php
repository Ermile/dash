<?php
namespace lib\utility;

/** Session: handle sessions in db **/
class sessionHandler implements \SessionHandlerInterface
{
	/**
	 * Author: Javad Evazzadeh | Evazzadeh.com
	 * This class writed for managing sessions on database
	 * and based on two below article
	 * http://php.net/manual/en/function.session-set-save-handler.php#118225
	 * http://shiflett.org/articles/storing-sessions-in-a-database
	 */

	// save link to database
	private $link;
	// save lifetime of sessions
	private $lifeTime;
	// save session name
	private $name;

	public $db_host = 'localhost';

	/**
	 * this function call on running sessions
	 * @param  [type] $_savePath    default save path for sessions
	 * @param  [type] $_sessionName name of session
	 * @return [type]               true if all is good
	 */
	public function open($_savePath, $_sessionName)
	{
		// generate database name
		$db_name = '_tools';
		if(defined('core_name') && constant('core_name'))
		{
			$db_name = constant('core_name'). $db_name;
		}
		else
		{
			$db_name = constant('db_name'). $db_name;
		}

		// get session-lifetime
		$this->lifeTime = get_cfg_var("session.gc_maxlifetime");
		$this->name     = $_sessionName;
		// connect to mysql and save link
		$link           = @mysqli_connect($this->db_host, db_user, db_pass, $db_name);
		// if error on connection to database
		if(!$link)
		{
			// if database not exist create it
			if(@mysqli_connect_errno() === 1049)
			{
				$link   = @mysqli_connect($this->db_host, db_user, db_pass, db_name);
				@mysqli_set_charset($link, "utf8");
				@mysqli_query($link, "SET collation_connection = utf8_general_ci");
				if($link)
				{
					$qry = "CREATE DATABASE if not exists ". $db_name;
					// try to create database
					if(!@mysqli_query($link, $qry))
					{
						// if cant create db
						return false;
					}
					// else if can create new database then reset link to dbname
					$link   = @mysqli_connect($this->db_host, db_user, db_pass, $db_name);
				}
			}
			else
			{
				// another error occur
				return false;
			}
		}

		// check if table not exist, create sessions table
		$qry =
			"CREATE TABLE if not exists `sessions` (
				`id` varchar(32) NOT NULL,
				`session_name` varchar(32) NOT NULL,
				`session_create` datetime NOT NULL,
				`session_expire` datetime NOT NULL,
				`session_data` text,
				`session_meta` mediumtext,
				`date_modified` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
				PRIMARY KEY (`id`)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;";
		if(!@mysqli_query($link, $qry))
		{
			// error on creating table
			return false;
		}

		// if link is exist set it as global variable
		if($link)
		{
			$this->link = $link;
			return true;
		}

		return false;
	}

	/**
	 * close session
	 * @return [type] [description]
	 */
	public function close()
	{
		if($this->link)
		{
			@mysqli_close($this->link);
		}

		return true;
	}

	/**
	 * read session data
	 * @param  [type] $_id unique code of the session
	 * @return [type]      the value of session
	 */
	public function read($_id)
	{
		$_id = @mysqli_real_escape_string($this->link, $_id);

		$qry =
		"SELECT session_data
			FROM sessions
			WHERE id = '$_id' AND session_name = '". $this->name. "' AND session_expire > '".date('Y-m-d H:i:s')."'";
		$result = @mysqli_query($this->link, $qry);

		if($result && $row = @mysqli_fetch_assoc($result))
		{
			return $row['session_data'];
		}
		else
		{
			return '';
		}
	}

	/**
	 * write new session into database
	 * @param  [type] $_id   unique id
	 * @param  [type] $_data the data want to save
	 * @return [type]        true if all is good
	 */
	public function write($_id, $_data)
	{
		$_id         = @mysqli_real_escape_string($this->link, $_id);
		$_data       = @mysqli_real_escape_string($this->link, $_data);


		$time_create    = date('Y-m-d H:i:s');
        // new session-expire-time
        $time_expire = date('Y-m-d H:i:s', time() + $this->lifeTime);

		$qry         =
		"REPLACE INTO sessions
			SET id = '$_id',
				session_name = '". $this->name. "',
				session_create = '$time_create',
				session_expire = '$time_expire',
				session_data = '$_data'
		";
		$result      = @mysqli_query($this->link, $qry);
		if($result)
		{
			return true;
		}
		else
		{
			return false;
		}
    }

    /**
     * destroy the session with its id
     * @param  [type] $_id unique id
     * @return [type]      true if all is good
     */
	public function destroy($_id)
	{
		$_id    = @mysqli_real_escape_string($this->link, $_id);
		$qry    = "DELETE FROM sessions WHERE id ='$_id' AND session_name ='". $this->name. "';";
		$result = @mysqli_query($this->link, $qry);
		if($result)
		{
			return true;
		}
		else
		{
			return false;
		}
	}

	/**
	 * delete session older than specefic time
	 * @param  [type] $_max the max age of session
	 * @return [type]       true if all is good
	 */
	public function gc($_max)
	{
		$qry =
		"DELETE
			FROM sessions
			WHERE ((UNIX_TIMESTAMP(session_expire) + $_max) < $_max);
			AND session_name = '". $this->name. "';";

		$result = @mysqli_query($this->link, $qry);
		if($result)
		{
			return true;
		}
		else
		{
			return false;
		}
	}
}
?>