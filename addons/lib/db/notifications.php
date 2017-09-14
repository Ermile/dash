<?php
namespace lib\db;

/** notifications managing **/
class notifications
{
	/**
	 * insert new notification
	 *
	 * @return     <type>  ( description_of_the_return_value )
	 */
	public static function insert()
	{
		return \lib\db\config::public_insert('notifications', ...func_get_args());
	}


	/**
	 * make multi insert
	 *
	 * @return     <type>  ( description_of_the_return_value )
	 */
	public static function multi_insert()
	{
		return \lib\db\config::public_multi_insert('notifications', ...func_get_args());
	}


	/**
	 * update the notification
	 *
	 * @return     <type>  ( description_of_the_return_value )
	 */
	public static function update()
	{
		return \lib\db\config::public_update('notifications', ...func_get_args());
	}


	/**
	 * get the notification
	 *
	 * @return     <type>  ( description_of_the_return_value )
	 */
	public static function get()
	{
		return \lib\db\config::public_get('notifications', ...func_get_args());
	}


	/**
	 * Searches for the first match.
	 *
	 * @return     <type>  ( description_of_the_return_value )
	 */
	public static function search()
	{
		$result = \lib\db\config::public_search('notifications', ...func_get_args());
		return $result;
	}


	/**
	 * Gets not sended notifications
	 *
	 * @return     <type>  Not sended.
	 */
	public static function get_not_sended()
	{
		$query = "SELECT * FROM notifications WHERE notifications.senddate IS NULL";
		return \lib\db::get($query);
	}


	/**
	 * get unread notify
	 *
	 * @param      <type>   $_user_id  The user identifier
	 * @param      boolean  $_count    The count
	 */
	public static function unread($_user_id, $_count = false)
	{
		if(!$_user_id || !is_numeric($_user_id))
		{
			return false;
		}
		if($_count)
		{
			$select = " COUNT(notifications.id) AS `count` ";
		}
		else
		{
			$select = " * ";
		}

		$query = " SELECT $select FROM notifications WHERE notifications.read IS NULL AND notifications.user_id = $_user_id ";

		if($_count)
		{
			return \lib\db::get($query, 'count', true);
		}
		else
		{
			return \lib\db::get($query);
		}
	}


	/**
	 * notifycation
	 *
	 * @var        array
	 */
	public static $NOTIFICATIONS = [];

	/**
	 * Sets the multi record.
	 */
	public static function set_multi_record()
	{
		if(!empty(self::$NOTIFICATIONS))
		{
			return self::multi_insert(self::$NOTIFICATIONS);
		}
	}


	/**
	 * set new notify
	 *
	 * @param      <type>  $_args  The arguments
	 */
	public static function set($_args)
	{
		if(!is_array($_args))
		{
			$_args = [];
		}

		$default_args =
		[
			'to'              => null,
			'needanswer'      => null,
			'answer'          => null,
			'content'         => null,
			'title'           => null,
			'cat'             => null,
			'from'            => null,
			'url'             => null,
			'related_foreign' => null,
			'related_id'      => null,
			'read'            => null,
			'status'          => 'enable',
			'expiredate'      => null,
			'desc'            => null,
			'meta'            => null,
			'telegram'        => false,
			'sms'             => false,
			'email'           => false,
			// send multi query
			'multi'           => false,
		];

		$_args = array_merge($default_args, $_args);

		if(!$_args['to']) return false;
		if(!isset($_args['content'])) return false;
		if(!$_args['cat']) return false;

		$cat_detail         = [];
		$cat_id             = null;

		$all_cat_list       = \lib\option::config('notification', 'cat');
		$all_cat_list_title = array_column($all_cat_list, 'title');
		$all_cat_list_title = array_combine(array_keys($all_cat_list), $all_cat_list_title);

		if(($key = array_search($_args['cat'], $all_cat_list_title)) !== false)
		{
			$cat_detail = $all_cat_list[$key];
			$cat_id = $key;
			if(isset($cat_detail['send_by']) && is_array($cat_detail['send_by']))
			{
				foreach ($cat_detail['send_by'] as $value)
				{
					$_args[$value] = true;
				}
			}
		}

		$insert =
		[
			'user_id'         => $_args['to'],
			'user_idsender'   => $_args['from'],
			'title'           => $_args['title'],
			'content'         => $_args['content'],
			'category'        => $cat_id,
			'telegram'        => $_args['telegram'] ? 1 : null,
			'sms'             => $_args['sms'] ? 1 : null,
			'email'           => $_args['email'] ? 1 : null,
			'url'             => $_args['url'],
			'read'            => $_args['read'],
			'status'          => $_args['status'],
			'expiredate'      => $_args['expiredate'],
			'desc'            => $_args['desc'],
			'meta'            => $_args['meta'],
			'related_foreign' => $_args['related_foreign'],
			'related_id'      => $_args['related_id'],
			'needanswer'      => $_args['needanswer'],
			'answer'          => $_args['answer'],
		];

		if($_args['multi'])
		{
			self::$NOTIFICATIONS[] = $insert;
		}
		else
		{
			return self::insert($insert);
		}
	}
}
?>