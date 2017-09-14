<?php
namespace lib\db;

/** work with posts **/
class posts
{

	use posts\search;

	/**
	 * this library work with posts
	 * v1.0
	 */


	/**
	 * insert new recrod in posts table
	 * @param array $_args fields data
	 * @return mysql result
	 */
	public static function insert()
	{
		return \lib\db\config::public_insert('posts', ...func_get_args());
	}


	/**
	 * update field from posts table
	 * get fields and value to update
	 * @param array $_args fields data
	 * @param string || int $_id record id
	 * @return mysql result
	 */
	public static function update()
	{
		return \lib\db\config::public_update('posts', ...func_get_args());
	}


	/**
	 * we can not delete a record from database
	 * we just update field status to 'deleted' or 'deleted' or set this record to black list
	 * @param string || int $_id record id
	 * @return mysql result
	 */
	public static function delete($_id)
	{
		// get id
		$query = "
				UPDATE  posts
				SET posts.status = 'deleted'
				WHERE posts.id = $_id
				";

		return \lib\db::query($query);
	}


	/**
	 * Gets one record of post
	 *
	 * @param      <type>  $_post_id  The post identifier
	 *
	 * @return     <type>  One.
	 */
	public static function get_one($_post_id)
	{
		$query = "SELECT * FROM posts WHERE id = $_post_id LIMIT 1";
		$result = \lib\db::get($query);
		$result = \lib\utility\filter::meta_decode($result);
		if(isset($result[0]))
		{
			$result = $result[0];
		}
		return $result;
	}


	/**
	 * Gets some identifier.
	 * get some posts by id
	 * @param      <type>   $_ids   The identifiers
	 *
	 * @return     boolean  Some identifier.
	 */
	public static function get_some_id($_ids)
	{
		if(!$_ids)
		{
			return false;
		}

		if(is_array($_ids))
		{
			$_ids = implode(',', $_ids);
		}

		$result = \lib\db::get("SELECT * FROM posts WHERE id IN ($_ids)");
		$result = \lib\utility\filter::meta_decode($result);
		return $result;
	}


	/**
	 * Determines if attachment.
	 *
	 * @param      <type>  $_id    The identifier
	 */
	public static function is_attachment($_id)
	{
		if(!$_id || !is_numeric($_id))
		{
			return false;
		}

		$query =
		"
			SELECT * FROM posts
			WHERE id = $_id
			AND type = 'attachment'
			AND posts.status IN ('draft', 'publish')
			LIMIT 1
		";
		$result = \lib\db::get($query, null, true);

		if($result)
		{
			if(isset($result['meta']) && substr($result['meta'], 0,1) === '{')
			{
				$result['meta'] = json_decode($result['meta'], true);
			}
			return $result;
		}
		return false;
	}


	/**
	 * get list of polls
	 * @param  [type] $_user_id set userid
	 * @param  [type] $_return  set return field value
	 * @param  string $_type    set type of post
	 * @return [type]           an array or number
	 */
	public static function get()
	{
		return \lib\db\config::public_get('posts', ...func_get_args());
	}
}
?>