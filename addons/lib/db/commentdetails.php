<?php
namespace lib\db;

/** commentdetails managing **/
class commentdetails
{
	/**
	 * this library work with commentdetails
	 * v1.0
	 */


	/**
	 * insert new tag in commentdetails table
	 * @param array $_args fields data
	 * @return mysql result
	 */
	public static function insert()
	{
		return \lib\db\config::public_insert('commentdetails', ...func_get_args());
	}


	/**
	 * check user id scored the comment
	 *
	 * @param      <type>  $_user_id     The user identifier
	 * @param      <type>  $_comment_id  The comment identifier
	 */
	public static function check($_user_id, $_comment_id)
	{
		$query =
		"
			SELECT
				type
			FROM
				commentdetails
			WHERE
				user_id    = $_user_id AND
				comment_id = $_comment_id
			LIMIT 1
		";
		$result = \lib\db::get($query, 'type', true);
		return $result;
	}


	/**
	 * upate user score
	 *
	 * @param      <type>  $_user_id    The user identifier
	 * @param      <type>  $comment_id  The comment identifier
	 * @param      <type>  $_type       The type
	 */
	public static function update($_user_id, $_comment_id, $_old_type, $_new_type)
	{
		$query =
		"
			UPDATE
				commentdetails
			SET
				type = '$_new_type'
			WHERE
				user_id    = $_user_id AND
				comment_id = $_comment_id AND
				type       = '$_old_type'
			LIMIT 1
		";
		$result = \lib\db::query($query);
		return $result;
	}


	/**
	 * set score to comments
	 *
	 * @param      <type>  $_user_id    The user identifier
	 * @param      <type>  $comment_id  The comment identifier
	 * @param      <type>  $_type       The type
	 */
	public static function set($_user_id, $_comment_id, $_type)
	{
		$result = false;

		$old_type = self::check($_user_id, $_comment_id);
		if(!$old_type)
		{
			$query =
			[
				'user_id'    => $_user_id,
				'comment_id' => $_comment_id,
				'type'       => "$_type"
			];
			$result =  self::insert($query);
			$update_comment = self::set_comment_data($_comment_id, $_type);
		}
		else
		{
			if($_type != $old_type)
			{
				$result =  self::update($_user_id, $_comment_id, $old_type, $_type);
				$update_comment = self::set_comment_data($_comment_id, $_type, true);
			}
		}
		return $result;
	}


	/**
	 * Sets the comment data.
	 *
	 * @param      <type>  $_comment_id  The comment identifier
	 * @param      <type>  $_type        The type
	 */
	public static function set_comment_data($_comment_id, $_type, $_update = false)
	{
		if($_type != 'minus' && $_type != 'plus')
		{
			return false;
		}

		$set = [];
		$set[] = " comment_$_type = IF(comment_$_type IS NULL, 1, comment_$_type + 1) ";

		if($_update)
		{
			$reverse = 'minus';
			if($_type == 'minus')
			{
				$reverse = 'plus';
			}
			$set[] = " comment_$reverse = IF(comment_$reverse IS NULL, 0, comment_$reverse - 1) ";
		}
		$set = join($set, ', ');
		$query =
		"
			UPDATE
				comments
			SET
				$set
			WHERE
				id = $_comment_id
		";
		return \lib\db::query($query);
	}
}
?>