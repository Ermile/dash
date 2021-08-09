<?php
namespace dash\db;

/** work with posts **/
class posts
{

	use \dash\db\posts\search;

	public static function all_post_title($_where)
	{
		$where = null;
		if($_where)
		{
			$where = " WHERE ". \dash\db\config::make_where($_where);
		}

		$query = "SELECT posts.title, posts.id FROM posts $where";
		$result = \dash\db::get($query);
		return $result;
	}


	public static function get_words_chart($_where = [])
	{
		$where = null;
		if($_where)
		{
			$where = " WHERE ". \dash\db\config::make_where($_where);
		}

		$query = "SELECT posts.title, posts.content FROM posts $where";
		$result = \dash\db::get($query);
		return $result;
	}


	public static function load_one_post($_id)
	{
		$query = "SELECT * FROM posts WHERE posts.id = $_id LIMIT 1";
		$result = \dash\db::get($query, null, true);
		return $result;
	}


	public static function get_post_counter($_args)
	{
		$where       = \dash\db\config::make_where($_args);
		$query_where = null;

		if($where)
		{
			$query_where = "WHERE ". $where;
		}

		$query =
		"
			SELECT
				COUNT(*) AS `count`,
				posts.status
			FROM
				posts
				$query_where
			GROUP BY posts.status
		";

		$result = \dash\db::get($query, ['status', 'count']);
		return $result;

	}


	public static function get_count()
	{
		return \dash\db\config::public_get_count('posts', ...func_get_args());
	}

	/**
	 * insert new recrod in posts table
	 * @param array $_args fields data
	 * @return mysql result
	 */
	public static function insert()
	{
		\dash\db\config::public_insert('posts', ...func_get_args());
		return \dash\db::insert_id();
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
		return \dash\db\config::public_update('posts', ...func_get_args());
	}


	public static function home_chart()
	{
		$query =
		"
			SELECT
				COUNT(*) AS `count`,
				posts.type AS `type`
			FROM
				posts
			GROUP BY posts.type
		";

		$result = \dash\db::get($query);
		return $result;
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

		return \dash\db::query($query);
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
		$result = \dash\db::get($query);
		$result = \dash\utility\filter::meta_decode($result);
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

		$result = \dash\db::get("SELECT * FROM posts WHERE id IN ($_ids)");
		$result = \dash\utility\filter::meta_decode($result);
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
		$result = \dash\db::get($query, null, true);

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
		return \dash\db\config::public_get('posts', ...func_get_args());
	}


	public static function get_special_post($_options = [])
	{
		$limit = "LIMIT $_options[limit]";

		$lang = \dash\language::current();

		if(isset($_options['lang']))
		{
			$lang = $_options['lang'];
		}

		$where = null;
		if(isset($_options['where']))
		{
			$make_where = \dash\db\config::make_where($_options['where']);
			if($make_where)
			{
				$where = " AND ". $make_where;
			}
		}

		$offset = null;
		if(isset($_options['offset']) && is_numeric($_options['offset']))
		{
			$offset = " OFFSET $_options[offset]";
		}

		$time = time();
		$query =
		"
			SELECT
				*
			FROM
				posts
			WHERE
				posts.status   = 'publish' AND
				posts.type     = 'post' AND
				posts.language = '$lang' AND
				UNIX_TIMESTAMP(posts.publishdate) <= $time
				$where
			ORDER BY posts.publishdate DESC
			$limit $offset
		";
		return \dash\db::get($query);
	}


	public static function get_last_posts($_options = [])
	{
		$time = time();

		$limit = "LIMIT $_options[limit]";

		$lang = \dash\language::current();
		if(isset($_options['lang']))
		{
			$lang = $_options['lang'];
		}

		$where = null;
		if(isset($_options['where']))
		{
			$make_where = \dash\db\config::make_where($_options['where']);
			if($make_where)
			{
				$where = " AND ". $make_where;
			}
		}

		if($_options['pagenation'])
		{
			$pagenation_query =
			"
				SELECT
					COUNT(*) AS `count`
				FROM
					posts
				WHERE
					posts.status            = 'publish' AND
					posts.type              = 'post' AND
					posts.language          = '$lang' AND
					UNIX_TIMESTAMP(posts.publishdate) <= $time
					$where
			";

			$pagenation_query = \dash\db::get($pagenation_query, 'count', true);
			list($limit_start, $limit) = \dash\db::pagnation((int) $pagenation_query, $_options['limit']);
			$limit = " LIMIT $limit_start, $limit ";
		}

		$offset = null;
		if(isset($_options['offset']) && is_numeric($_options['offset']))
		{
			$offset = " OFFSET $_options[offset]";
		}

		$query =
		"
			SELECT
				*
			FROM
				posts
			WHERE
				posts.status            = 'publish' AND
				posts.type              = 'post' AND
				posts.language          = '$lang' AND
				UNIX_TIMESTAMP(posts.publishdate) <= $time
				$where
			ORDER BY posts.publishdate DESC
			$limit $offset
		";
		return \dash\db::get($query);
	}


	public static function get_posts_term($_options = [], $_type = null)
	{
		$time = time();

		$default_options =
		[
			'limit'  => 10,
			'cat'    => null,
			'tag'    => null,
			'term'   => null,
			'type'   => 'post',
			'random' => false,
			'where' => null,
		];

		if(!is_array($_options))
		{
			$_options = [];
		}

		$_options = array_merge($default_options, $_options);


		$my_query = null;

		$lang = \dash\language::current();
		if(isset($_options['lang']))
		{
			$lang = $_options['lang'];
		}

		switch ($_type)
		{
			case 'cat':
			case 'help':
				if(!$_options['cat'])
				{
					return false;
				}
				$my_query = " terms.slug = '$_options[cat]' AND ";
				break;

			case 'tag':
			case 'help_tag':
				if(!$_options['tag'])
				{
					return false;
				}
				$my_query = " terms.slug = '$_options[tag]' AND ";
				break;

			case 'term':
				if(!$_options['term'])
				{
					return false;
				}
				$my_query = " terms.id = '$_options[term]' AND ";
				break;

			default:
				return false;
				break;
		}

		$order = " posts.publishdate DESC ";
		if($_options['random'])
		{
			$order = " RAND() ";
		}

		$where = null;
		if(isset($_options['where']))
		{
			$make_where = \dash\db\config::make_where($_options['where']);
			if($make_where)
			{
				$where = " AND ". $make_where;
			}
		}

		$offset = null;
		if(isset($_options['offset']) && is_numeric($_options['offset']))
		{
			$offset = " OFFSET $_options[offset]";
		}

		$query =
		"
			SELECT
				posts.*
			FROM
				posts
			WHERE
				posts.id IN
				(
					SELECT
						termusages.related_id
					FROM
						termusages
					INNER JOIN terms ON terms.id = termusages.term_id
					WHERE
						terms.type = '$_type' AND
						$my_query
						termusages.related_id = posts.id
				) AND
				posts.status            = 'publish' AND
				posts.type              = '$_options[type]' AND
				posts.language          = '$lang' AND
				UNIX_TIMESTAMP(posts.publishdate) <= $time
				$where
			ORDER BY $order
			LIMIT $_options[limit] $offset
		";

		$result = \dash\db::get($query);
		return $result;
	}


	public static function get_post_similar($_post_id, $_lang, $_subdomain = false)
	{
		if(!$_post_id || !is_numeric($_post_id))
		{
			return false;
		}

		$where = null;
		$wherePost = null;
		if($_subdomain !== false)
		{
			if($_subdomain)
			{
				$where = " AND terms.subdomain = '$_subdomain' ";
				$wherePost = " AND posts.subdomain = '$_subdomain' ";
			}
			else
			{
				$where = " AND terms.subdomain IS NULL ";
				$wherePost = " AND posts.subdomain IS NULL ";
			}
		}


		$load_post_term =
		"
			SELECT
				terms.id AS `id`
			FROM
				posts
			INNER JOIN termusages ON termusages.related_id = posts.id AND termusages.related = 'posts'
			INNER JOIN terms ON terms.id = termusages.term_id
			WHERE
				posts.id = $_post_id
				$where
		";

		$post_term = \dash\db::get($load_post_term, 'id');
		if(empty($post_term))
		{
			return null;
		}

		$post_term = implode(',', $post_term);
		$time      = time();

		$query =
		"
			SELECT
				posts.title,
				posts.url,
				posts.id
			FROM
				posts
			INNER JOIN termusages ON termusages.related_id = posts.id AND termusages.related = 'posts'
			INNER JOIN terms ON terms.id = termusages.term_id
			WHERE
				termusages.related_id             != $_post_id AND
				termusages.term_id IN ($post_term) AND
				termusages.related                = 'posts' AND
				posts.status                      = 'publish' AND
				posts.type                        = 'post' AND
				posts.language                    = '$_lang' AND
				UNIX_TIMESTAMP(posts.publishdate) <= $time
				$wherePost
			GROUP BY posts.title, posts.url, posts.id
			ORDER BY posts.id DESC
			LIMIT 5
		";

		$result = \dash\db::get($query);
		return $result;

	}
}
?>
