<?php
namespace dash\app\posts;


trait get
{
	public static function get_category_tag($_post_id, $_type, $_related = 'posts')
	{
		$post_id = \dash\coding::decode($_post_id);
		if(!$post_id)
		{
			return false;
		}

		$result = \dash\db\termusages::usage($post_id, $_type, $_related);

		$temp = [];
		if(is_array($result))
		{
			foreach ($result as $key => $value)
			{
				$temp[] = self::ready($value);
			}
		}

		return $temp;
	}

	/**
	 * Gets the user.
	 *
	 * @param      <type>  $_args  The arguments
	 *
	 * @return     <type>  The user.
	 */
	public static function get($_id, $_options = [])
	{
		$default_options =
		[
			'debug'       => true,
			'check_login' => true,
		];

		if(!is_array($_options))
		{
			$_options = [];
		}

		$_options = array_merge($default_options, $_options);

		if($_options['check_login'])
		{
			if(!\dash\user::id())
			{
				return false;
			}
		}

		$id = \dash\coding::decode($_id);

		if(!$id)
		{
			\dash\notif::error(T_("Invalid posts id"));
			return false;
		}

		$detail = \dash\db\posts::get(['id' => $id, 'limit' => 1]);

		$temp = [];

		if(is_array($detail))
		{
			$temp = self::ready($detail);
		}

		return $temp;
	}


	public static function load_post($_id)
	{
		$id = \dash\coding::decode($_id);
		if(!$id || !is_numeric($id))
		{
			return false;
		}

		$load = \dash\db\posts::load_one_post($id);

		if(!$load || !isset($load['user_id']) || !isset($load['type']))
		{
			return false;
		}

		$myPost = false;
		if(intval($load['user_id']) === intval(\dash\user::id()))
		{
			$myPost = true;
		}

		if(!$myPost)
		{
			switch ($load['type'])
			{
				case 'help':
					if(!\dash\permission::check('cpHelpCenterViewAll'))
					{
						return false;
					}
					break;

				case 'post':
					if(!\dash\permission::check('cpPostsViewAll'))
					{
						return false;
					}
					break;
			}
		}

		$load = self::ready($load);

		return $load;
	}


	public static function get_post_list($_options = [])
	{
		$default_options =
		[
			'limit'      => 10,
			'cat'        => null,
			'tag'        => null,
			'term'       => null,
			'pagenation' => false,
			'special'    => false,
			'mode'       => null,
			'post_id'    => null,
			'subtype'    => null,
			'offset'     => null,
		];


		if(!is_array($_options))
		{
			$_options = [];
		}

		$_options = array_merge($default_options, $_options);

		if(!\dash\option::config('no_subdomain'))
		{
			$subdomain = \dash\url::subdomain();
			if($subdomain)
			{
				$_options['where']['subdomain'] = $subdomain;
			}
			else
			{
				$_options['where']['subdomain'] = null;
			}
		}

		if($_options['subtype'])
		{
			$_options['where']['subtype'] = $_options['subtype'];
		}

		if($_options['special'] !== false)
		{
			$_options['where']['special'] = $_options['special'];
		}

		$get_last_posts = [];

		if($_options['mode'] === 'similar')
		{
			if(isset($_options['post_id']))
			{
				$get_last_posts = \dash\db\posts::get_post_similar(\dash\coding::decode($_options['post_id']), \dash\language::current());
			}
		}
		elseif($_options['cat'])
		{
			$get_last_posts = \dash\db\posts::get_posts_term($_options, 'cat');
		}
		elseif($_options['tag'])
		{
			$get_last_posts = \dash\db\posts::get_posts_term($_options, 'tag');
		}
		elseif($_options['term'])
		{
			$_options['term'] = \dash\coding::decode($_options['term']);
			$get_last_posts   = \dash\db\posts::get_posts_term($_options, 'term');
		}
		elseif($_options['special'] !== false)
		{
			$get_last_posts = \dash\db\posts::get_special_post($_options);
		}
		else
		{
			$get_last_posts = \dash\db\posts::get_last_posts($_options);
		}

		$temp = [];
		if(is_array($get_last_posts))
		{
			foreach ($get_last_posts as $key => $value)
			{
				$temp[] = self::ready($value);
			}
		}
		return $temp;
	}
}
?>