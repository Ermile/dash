<?php
namespace lib\db;

/** comments managing **/
class comments
{
	/**
	 * this library work with comments
	 * v1.0
	 */


	/**
	 * insert new recrod in comments table
	 * @param array $_args fields data
	 * @return mysql result
	 */
	public static function insert()
	{
		return \lib\db\config::public_insert('comments', ...func_get_args());
	}


	/**
	 * Searches for the first match.
	 *
	 * @return     <type>  ( description_of_the_return_value )
	 */
	public static function search()
	{
		return \lib\db\config::public_search('comments', ...func_get_args());
	}


	/**
	 * get the comment
	 *
	 * @return     <type>  ( description_of_the_return_value )
	 */
	public static function get()
	{
		return \lib\db\config::public_get('comments', ...func_get_args());
	}

	/**
	 * update field from comments table
	 * get fields and value to update
	 * @param array $_args fields data
	 * @param string || int $_id record id
	 * @return mysql result
	 */
	public static function update()
	{
		return \lib\db\config::public_update('comments', ...func_get_args());
	}


	/**
	 * we can not delete a record from database
	 * we just update field status to 'deleted' or 'disable' or set this record to black list
	 * @param string || int $_id record id
	 * @return mysql result
	 */
	public static function delete($_id)
	{
		// get id
		$query = "
				UPDATE comments
				SET comments.status = 'deleted'
				WHERE comments.id = $_id
				";

		return \lib\db::query($query);
	}


	/**
	 * save a comments
	 *
	 * @param      <type>  $_content  The content
	 * @param      array   $_args     The arguments
	 *
	 * @return     <type>  ( description_of_the_return_value )
	 */
	public static function save($_content, $_args = null)
	{
		$values =
		[
			"post_id"    => null,
			"author"     => null,
			"email"      => null,
			"url"        => null,
			// "content" => null,
			"meta"       => null,
			"status"     => null,
			"parent"     => null,
			"user_id"    => null,
			"visitor_id" => null,
		];

		if(!$_args)
		{
			$_args = [];
		}
		// foreach args if isset use it
		foreach ($_args as $key => $value)
		{
			$value = "'". $value. "'";
			// check in normal condition exist
			if(array_key_exists($key, $values))
			{
				$values[$key] = $value;
			}
			// check for id
			$newKey = $key.'_id';
			if(array_key_exists($newKey, $values))
			{
				$values[$newKey] = $value;
			}
			// check for table prefix
			$newKey = ''. $key;
			if(array_key_exists($newKey, $values))
			{
				$values[$newKey] = $value;
			}
		}
		foreach ($values as $key => $value)
		{
			if(!$value)
			{
				unset($values[$key]);
			}
		}

		// set not null fields
		// set comment content
		$values['content'] = "'". htmlspecialchars($_content). "'";
		// set comment status if not set
		if(!isset($values['status']))
		{
			$values['status'] = "'unapproved'";
		}
		// set time of comment
		if(isset($values['meta']) && is_array($values['meta']))
		{
			$values['meta']['time'] = date('Y-m-d H:i:s');
		}
		else
		{
			$values['meta'] = ['time' => date('Y-m-d H:i:s')];
		}
		$values['meta'] = "'".json_encode($values['meta'], JSON_UNESCAPED_UNICODE)."'";
		// generate query text
		$list_field  = array_keys($values);
		$list_field  = implode($list_field, ', ');
		$list_values = implode($values, ', ');
		// create query string
		$qry       = "INSERT INTO comments ( $list_field ) VALUES ( $list_values )";
		var_dump($qry);
		// run query and insert into db
		$result    = \lib\db::query($qry);
		// get insert id
		$commentId = \lib\db::insert_id();
		// return last insert id
		return $commentId;
	}


	/**
	 * Gets the post comment.
	 *
	 * @param      <type>   $_post_id  The post identifier
	 * @param      integer  $_limit    The limit
	 * @param      boolean  $_user_id  The user identifier
	 *
	 * @return     <type>   The post comment.
	 */
	public static function get_comment($_post_id, $_limit = 6, $_user_id = false)
	{
		if(!is_numeric($_limit))
		{
			$_limit = 6;
		}

		if($_user_id)
		{
			$_limit = $_limit - 1;
		}

		$query =
		"
		(
			SELECT
				*
			FROM
				comments
			WHERE
				comments.post_id        = $_post_id AND
				comments.status = 'approved' AND
				comments.type   = 'comment'
			ORDER BY RAND()
			LIMIT $_limit
		)
		";
		if($_user_id)
		{
			$query .=
			"
			UNION ALL (
			SELECT
				*
			FROM
				comments
			WHERE
				comments.post_id      = $_post_id AND
				comments.user_id      = $_user_id AND
				comments.type = 'comment'
			ORDER BY comments.id DESC
			LIMIT 1
			)
			";
		}

		return self::select($query,"get");
	}


	/**
	 * Determines if rate.
	 *
	 * @param      <type>  $_user_id  The user identifier
	 * @param      <type>  $_post_id  The post identifier
	 */
	public static function is_rate($_user_id, $_post_id)
	{
		$query =
		"
			SELECT
				id
			FROM
				comments
			WHERE
				user_id = $_user_id AND
				post_id = $_post_id AND
				type = 'rate'
			LIMIT 1;
		";
		$rate = \lib\db::get($query, 'id', true);
		return $rate;
	}


	/**
	 * save rate to poll
	 *
	 * @param      <type>   $_user_id  The user identifier
	 * @param      <type>   $_post_id  The post identifier
	 * @param      integer  $_rate     The rate
	 *
	 * @return     boolean  ( description_of_the_return_value )
	 */
	public static function rate($_user_id, $_post_id, $_rate)
	{
		$is_rate = self::is_rate($_user_id, $_post_id);
		if($is_rate)
		{
			return true;
		}

		if(intval($_rate) < 0)
		{
			return false;
		}

		if(intval($_rate) > 5)
		{
			$_rate = 5;
		}

		$args =
		[
			'content' => $_rate,
			'type'    => 'rate',
			'status'  => 'approved',
			'user_id'         => $_user_id,
			'post_id'         => $_post_id
		];
		// insert comments
		$result = self::insert($args);

		if($result)
		{

			$total_rate = self::get_total_rate($_post_id);
			if(!$total_rate)
			{
				// insert new value
				$first_meta =
				[
					'total' =>
					[
						'count' => 1,
						'sum'   => $_rate,
						'avg'   => round($_rate / 1, 1)
					],
					"rate$_rate" =>
					[
						'count' => 1,
						'sum'   => $_rate,
						'avg'   => round($_rate / 1, 1)
					]
				];
				$first_meta = json_encode($first_meta, JSON_UNESCAPED_UNICODE);

				$arg =
				[
					'post_id'      => $_post_id,
					'cat'   => "poll_$_post_id",
					'key'   => 'comment',
					'value' => 'rate',
					'meta'  => $first_meta
				];
				return \lib\db\options::insert($arg);
			}
			else
			{
				$id = $total_rate['id'];
				$meta      = json_decode($total_rate['meta'], true);

				if(!is_array($meta))
				{
					return false;
				}

				foreach ($meta as $key => $value)
				{
					if($key == 'total' || $key == "rate$_rate")
					{
						$meta[$key]['count'] = $meta[$key]['count'] + 1;
						$meta[$key]['sum']   = $meta[$key]['sum'] + $_rate;
						$meta[$key]['avg']   = round(floatval($meta[$key]['sum']) / floatval($meta[$key]['count']), 1);
					}
				}
				if(!isset($meta["rate$_rate"]))
				{
					$meta["rate$_rate"] =
					[
						'count' => 1,
						'sum'   => $_rate,
						'avg'   => round($_rate / 1, 1)
					];
				}
				return \lib\db\options::update(['meta' => json_encode($meta, JSON_UNESCAPED_UNICODE)], $id);
			}
		}
	}


	/**
	 * Gets the total rate.
	 *
	 * @param      <type>  $_post_id  The post identifier
	 *
	 * @return     <type>  The total rate.
	 */
	public static function get_total_rate($_post_id)
	{
		$query =
		"
			SELECT
				id,
				meta AS 'meta'
			FROM
				options
			WHERE
				user_id IS NULL AND
				post_id      = $_post_id AND
				cat   = 'poll_$_post_id' AND
				key   = 'comment' AND
				value = 'rate'
			LIMIT 1;
		";
		$result = \lib\db::get($query, null, true);
		return $result;
	}


	/**
	 * Gets all comments for  admin accept
	 *
	 * @param      integer  $_limit  The limit
	 *
	 * @return     <type>   All.
	 */
	public static function admin_get($_limit = 50)
	{
		if(!is_numeric($_limit))
		{
			$_limit = 50;
		}

		$pagenation_query =
		"SELECT	id	FROM comments WHERE	comments.type = 'comment' AND comments.status = 'unapproved'
		 -- comments::admin_get() for pagenation ";
		list($limit_start, $_limit) = \lib\db::pagnation($pagenation_query, $_limit);
		$limit = " LIMIT $limit_start, $_limit ";

		$query =
		"
			SELECT
				comments.*,
				posts.title AS 'title',
				posts.url  AS 'url',
				users.status AS 'status',
				users.email AS 'email'
			FROM
				comments
			INNER JOIN posts ON posts.id = comments.post_id
			INNER JOIN users ON users.id = comments.user_id
			WHERE
				comments.type = 'comment' AND
				comments.status = 'unapproved'
			ORDER BY id ASC
			$limit
			-- comments::admin_get()
		";
		return \lib\db::get($query);
	}
}
?>