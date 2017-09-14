<?php
namespace lib\view;

trait twigAddons
{
	/**
	 * add twig filter
	 * @param string $method [description]
	 */
	public function add_twig_filter($method)
	{
		if(!isset($this->twig['filter'])) $this->twig['filter'] = array();
		array_push($this->twig['filter'], $method);
	}


	/**
	 * add twig function
	 * @param string $method [description]
	 */
	public function add_twig_function($method)
	{
		if(!isset($this->twig['function'])) $this->twig['function'] = array();
		array_push($this->twig['function'], $method);
	}


	/**
	 * attach twig extentions
	 * @param  object $twig
	 */
	public function twig_Extentions($twig)
	{
		foreach ($this->twig as $key => $value)
		{
			$ext="add".ucfirst($key);
			foreach ($value as $k => $v)
			{
				$method_name = "twig_{$key}_$v";
				$twig->$ext($this->$method_name());
			}
		}
	}


	/**
	 * [twig_macro description]
	 * @param  [type] $name [description]
	 * @return [type]       [description]
	 */
	public function twig_macro($name)
	{
		if(!isset($this->data->twig_macro)) $this->data->twig_macro = array();
		if(array_search($name, $this->data->twig_macro) === false) array_push($this->data->twig_macro, $name);
	}


	/**
	 * twig custom filter for static file cache
	 */
	public function twig_filter_fcache()
	{
		return new \Twig_SimpleFilter('fcache', function ($string)
		{
			if(file_exists($string))
			{
				return $string.'?'.filemtime($string);
			}
		});
	}


	/**
	 * twig custom filter for convert date to jalai with custom format like php date func format
	 */
	public function twig_filter_jdate()
	{
		return new \Twig_SimpleFilter('jdate', function ($_string, $_format ="Y/m/d", $_convert = true)
		{
			return \lib\utility\jdate::date($_format, $_string, $_convert);
		});
	}


	/**
	 * twig custom filter for convert date to best type of showing on each language
	 * tdate means translated date
	 */
	public function twig_filter_tdate()
	{
		return new \Twig_SimpleFilter('tdate', function ($_string, $_format ="Y/m/d", $_convert = true)
		{
			$result = $_string;
			if($this->data->site['currentlang'] == 'fa')
			{
				$result = \lib\utility\jdate::date($_format, $_string, $_convert);
			}
			else
			{
				$result = date($_format, strtotime($_string));
			}

			return $result;
		});
	}


	/**
	 * twig custom filter for convert date to best type of showing
	 */
	public function twig_filter_sdate()
	{
		return new \Twig_SimpleFilter('sdate', function ($_string, $_max ="day", $_format ="Y/m/d")
		{
			return \lib\utility::humanTiming($_string, $_max, $_format, $this->data->site['currentlang']);
		});
	}


	/**
	 * twig custom filter for convert date to jalai with custom format like php date func format
	 */
	public function twig_filter_readableSize()
	{
		return new \Twig_SimpleFilter('readableSize', function ($_string, $_type = 'file', $_emptyTxt = null)
		{
			return \lib\utility\upload::readableSize($_string, $_type, $_emptyTxt);
		});
	}


	/**
	 * twig custom filter for convert date to jalai with custom format like php date func format
	 */
	public function twig_filter_persian()
	{
		return new \Twig_SimpleFilter('persian', function ($_number)
		{
			return \lib\utility\human::number($_number, $this->data->site['currentlang']);
		});
	}


	/**
	 * twig custom filter for convert date to jalai with custom format like php date func format
	 */
	public function twig_filter_fitNumber()
	{
		return new \Twig_SimpleFilter('fitNumber', function ($_number)
		{
			return \lib\utility\human::number($_number, $this->data->site['currentlang']);
		});
	}


	/**
	 * [twig_filter_exist description]
	 * @return [type] [description]
	 */
	public function twig_filter_exist()
	{
		return new \Twig_SimpleFilter('exist', function ($_file, $_alternative = null)
		{
			$result = \lib\utility\file::alternative($_file, $_alternative);
			return $result;
		});
	}


	public function twig_filter_humantime()
	{
		return new \Twig_SimpleFilter('humantime', function ()
		{
			$result = \lib\utility\human::time(...func_get_args());
			return $result;
		});
	}


	/**
	 * [twig_filter_decode description]
	 * @return [type] [description]
	 */
	public function twig_filter_decode()
	{
		return new \Twig_SimpleFilter('decode', function ($_array, $_key = null)
		{
			$result = json_decode($_array, true);
			if(is_array($result) && isset($result[$_key]))
			{
				$result = $result[$_key];
			}
			else
			{
				$result = $_array;
			}

			return $result;
		});
	}


	/**
	 * twig custom filter for dump with php
	 */
	public function twig_function_dump()
	{
		return new \Twig_SimpleFunction('dump', function()
		{

		});
	}


	/**
	 * [twig_function_result description]
	 * @return [type] [description]
	 */
	public function twig_function_result()
	{
		return new \Twig_SimpleFunction('result', function()
		{
			var_dump($this->model());
		});
	}


	/**
	 * [twig_function_language description]
	 * @return [type] [description]
	 */
	public function twig_function_langList()
	{
		return new \Twig_SimpleFunction('langList', function()
		{
			$result      = null;
			$html        = array_column(func_get_args(), 'html');
			$all         = array_column(func_get_args(), 'all');
			$onlyLink    = array_column(func_get_args(), 'onlyLink');
			$class       = array_column(func_get_args(), 'class');
			$langList    = $this->data->site['langlist'];
			$urlRoot     = $this->data->url->root;
			$urlContent  = $this->data->url->content;
			$urlPath     = $this->data->url->path;
			$currentlang = \lib\define::get_language();

			if(!$all)
			{
				unset($langList[$currentlang]);
			}

			if($html)
			{
				$lang_string = '';
				foreach ($langList as $key => $value)
				{
					$langPrefix     = \lib\define::get_current_language_string($key);
					$href           = $urlRoot. $langPrefix;
					$activeClass    = '';
					$urlPathCurrent = '';
					if($key === $currentlang)
					{
						$activeClass = " class='active'";
					}

					if($urlContent)
					{
						$href           .= '/'.$urlContent;
					}
					if($urlPath)
					{
						$href           .= '/'.$urlPath;
						$urlPathCurrent .= $urlPath;
					}
					$lang_string .= "<a href='". $href . "'$activeClass hreflang='$key' data-direct>";
					$lang_string .= $value;
					$lang_string .= "</a>";
				}

				if(!$onlyLink)
				{
					if(is_array($class) && isset($class[0]))
					{
						$class = $class[0];
					}
					if(!is_string($class) || !$class)
					{
						$class = '';
					}
					else
					{
						$class = ' '. $class;
					}

					$lang_string = "<nav class='langlist$class' data-xhr='langlist' data-url='$urlPathCurrent'>". $lang_string .'</nav>';
				}

				echo $lang_string;
			}
			else
			{
				return $langList;
			}
		});
	}


	/**
	 * [twig_function_breadcrumb description]
	 * @return [type] [description]
	 */
	public function twig_function_breadcrumb()
	{
		return new \Twig_SimpleFunction('breadcrumb', function ($_path = null, $_direct = null, $_homepage = true, $_hideLast = null)
		{
			// if user dont pass a path give it from controller
			if(!$_path)
			{
				$myurl = $this->model()->breadcrumb();
				$_path = $this->url('breadcrumb');
			}
			$direct = null;
			if($_direct === true)
			{
				$direct = "data-direct";
			}

			$currentUrl = null;
			$result     = '';
			if($_homepage || count($myurl))
			{
				$baseURL    = $this->data->url->base;
				if(\lib\router::get_repository_name() === 'content')
				{
					$result = '<a href="'. $baseURL. '" tabindex="-1" '. $direct.'><span class="fa fa-home"></span> '.T_('Homepage').'</a>';
				}
				else
				{
					$myContent     = substr(\lib\router::get_repository_name(), 8);
					$myContentName = $myContent;
					// if contetent name is exist use it as alternative
					if(isset($this->data->breadcrumb[$myContent]))
					{
						$myContentName = $this->data->breadcrumb[$myContent];
					}
					elseif($myContent === 'cp')
					{
						$myContentName = 'Control Panel';
					}
					$result = '<a href="'. $baseURL. '" tabindex="-1" '. $direct.'><span class="fa fa-home"></span> '.T_('Home').'</a>';
					$result .= '<a href="'. $baseURL.'/'. $myContent. '" tabindex="-1" '. $direct.'>'.T_($myContentName).'</a>';
				}

			}

			foreach ($myurl as $key => $part)
			{
				$currentUrl  .= $_path[$key].'/';
				$baseURLFull = $this->data->url->baseFull;
				$anchorUrl   = trim($baseURLFull.'/'.$currentUrl, '/');
				$location    = $part;
				// set title of each locations
				if(isset($this->data->breadcrumb[$location]))
				{
					$location = $this->data->breadcrumb[$location];
				}

				// if trans of exact text is exist use it
				if($location != T_($location))
				{
					$location = T_($location);
				}
				// else change all chars to lower and check to find trans, if exist use it
				elseif($location != T_(mb_strtolower($location)))
				{
					$location = T_(mb_strtolower($location));
					$location = ucwords($location);
				}
				// else change it to good text
				else
				{
					$location    = str_replace('-', ' ', $location);
					$location    = ucwords($location);
					$location    = str_replace('And', 'and', $location);
					$location    = T_($location);
				}

				if(end($myurl) === $part)
				{
					if($_hideLast)
					{
						// do nothing
					}
					else
					{
						$result .= "<a>$location</a>";
					}
				}
				else
				{
					$result .= "<a href='$anchorUrl' tabindex='-1'>". $location. "</a>";
				}
			}

			echo $result;
		});
	}


	/**
	 * [twig_function_posts description]
	 * @return [type] [description]
	 */
	public function twig_function_posts()
	{
		return new \Twig_SimpleFunction('posts', function()
		{
			$posts  = $this->model()->posts(...func_get_args());
			$html   = array_column(func_get_args(), 'html');
			$desc   = array_column(func_get_args(), 'desc');
			if($html && count($html) === 1)
			{
				$html = $html[0];
			}

			if($desc && count($desc) === 1)
			{
				$desc = $desc[0];
			}

			if($html)
			{
				$counter = 0;
				$result  = '';
				$content = '';
				foreach ($posts as $item)
				{
					$result .= "\n    ";
					$result .= "<article>";

					if($desc == 'all' || (is_numeric($desc) && $desc > $counter))
					{
						$result .= "<a href='/".$item['url']."'>".$item['title']."</a>";
						if(isset($item['content']))
						{
							$content = \lib\utility\excerpt::get($item['content']);
							if($content)
							{
								$result .= '<p>'. $content .'</p>';
							}
						}
					}
					else
					{
						$result .= "<a href='/".$item['url']."'>".$item['title']."</a>";
					}
					$result .= "</article>";
					// increase counter
					$counter++;
				}

				echo $result;
			}
			else
			{
				return $posts;
			}

		});
	}


	/**
	 * [twig_function_posts description]
	 * @return [type] [description]
	 */
	public function twig_function_tags()
	{
		return new \Twig_SimpleFunction('tags', function()
		{
			$tags = [];
			$args = func_get_args();
			if(isset($args[0]))
			{
				$args = $args[0];
			}

			// get post id
			if(!isset($args['post_id']))
			{
				if(isset($this->data->post['id']))
				{
					$args['post_id'] = $this->data->post['id'];
				}
			}
			// get tags
			if(isset($args['post_id']))
			{
				$tags = \lib\db\tags::usage($args['post_id']);
			}

			// check html mod
			if(isset($args['html']))
			{
				$html = '';
				foreach ($tags as $key => $value) {
					$html .= "<a href=\"$value\">$value</a>";
				}
				echo $html;
			}
			else
			{
				return $tags;
			}
		});
	}


	/**
	 * [twig_function_posts description]
	 * @return [type] [description]
	 */
	public function twig_function_comments()
	{
		return new \Twig_SimpleFunction('comments', function()
		{
			$comments = [];
			$args = func_get_args();
			if(isset($args[0]))
			{
				$args = $args[0];
			}

			// get post id
			if(!isset($args['post_id']))
			{
				if(isset($this->data->post['id']))
				{
					$args['post_id'] = $this->data->post['id'];
				}
			}
			// count of show comments
			$limit = 6;
			if(isset($args['limit']))
			{
				$limit = $args['limit'];
			}

			// get comments
			if(isset($args['post_id']))
			{
				$comments = \lib\db\comments::get_post_comment($args['post_id'], $limit, $this->login('id'));
			}
			return $comments;
		});
	}


	/**
	 * [twig_function_posts description]
	 * @return [type] [description]
	 */
	public function twig_function_post_search()
	{
		return new \Twig_SimpleFunction('post_search', function()
		{
			$post_search = [];
			$args = func_get_args();
			if(isset($args[0]))
			{
				$args = $args[0];
			}
			$post_search = \lib\db\posts::search(null, $args);
			return $post_search;

		});
	}


	/**
	 * [twig_function_posts description]
	 * @return [type] [description]
	 */
	public function twig_function_similar_post()
	{
		return new \Twig_SimpleFunction('similar_post', function()
		{
			$similar_post = [];
			$args = func_get_args();

			if(isset($args[0]))
			{
				$args = $args[0];
			}
			// get post id
			if(!isset($args['post_id']))
			{
				if(isset($this->data->post['id']))
				{
					$args['post_id'] = $this->data->post['id'];
				}
			}

			$options = [];

			// count of show similar
			$options['limit'] = 5;
			if(isset($args['limit']) && is_numeric($args['limit']))
			{
				$options['limit'] = $args['limit'];
			}

			if(isset($args['term_type']) && is_string($args['term_type']))
			{
				$options['term_type'] = $args['term_type'];
			}


			if(isset($args['post_privacy']) && is_string($args['post_privacy']))
			{
				$options['post_privacy'] = $args['post_privacy'];
			}


			if(isset($args['post_status']) && is_string($args['post_status']))
			{
				$options['post_status'] = $args['post_status'];
			}

			if(isset($args['termusage_foreign']) && is_string($args['termusage_foreign']))
			{
				$options['termusage_foreign'] = $args['termusage_foreign'];
			}

			if(isset($args['post_id']))
			{
				$similar_post = \lib\db\tags::get_post_similar($args['post_id'], $options);
			}

			if(isset($args['html']))
			{
				$html = '';
				foreach ($similar_post as $key => $value) {
					$html .= "<a href=\"$value[url]\">$value[title]</a>";
				}
				echo $html;
			}
			else
			{
				return $similar_post;
			}

		});
	}


	/**
	 * [twig shortURL decode|encode]
	 * @return [type] [description]
	 */
	public function twig_filter_shortURL()
	{
		return new \Twig_SimpleFilter('shortURL', function ($_url, $_type = 'decode', $_alphabet = null)
		{
			$result = null;
			if($_type === 'decode')
			{
				$result = \lib\utility\shortURL::decode($_url, $_alphabet);
			}
			elseif($_type === 'encode')
			{
				$result = \lib\utility\shortURL::encode($_url, $_alphabet);
			}
			return $result;
		});
	}


	public function twig_filter_filemtime()
	{
		return new \Twig_SimpleFilter('filemtime', function ($_url, $_withReturn = null)
		{
			$result       = '';
			$complete_url = root.'public_html/';
			if($_withReturn)
			{
				$complete_url .= 'static/';
			}
			$complete_url .= $_url;
			if($_url && \lib\utility\file::exists($complete_url))
			{
				$result = filemtime($complete_url);
			}

			if($_withReturn)
			{
				$result = $_url. '?'. $result;
			}

			return $result;
		});
	}


	/**
	 * return tha attachment record of post
	 *
	 * @return     \     ( description_of_the_return_value )
	 */
	public function twig_function_attachment()
	{
		return new \Twig_SimpleFunction('attachment', function()
		{
			$attachment = [];
			$args       = func_get_args();

			if(isset($args[0]))
			{
				$args = $args[0];
			}

			$get_url = false;
			if(isset($args['url']) && $args['url'] === true)
			{
				$get_url = true;
			}

			if(isset($args['id']))
			{
				$attachment = \lib\db\posts::get_one($args['id']);
				if(isset($attachment['post_type']) && $attachment['post_type'] != 'attachment')
				{
					return [];
				}

				if(is_array($attachment))
				{
					$tmp_attachment = [];
					foreach ($attachment as $key => $value)
					{
						$tmp_attachment[str_replace('post_', '', $key)] = $value;
					}
					$attachment = $tmp_attachment;
				}
			}
			if($get_url)
			{
				if(isset($attachment['meta']['url']))
				{
					return $this->url('static'). '/'. $attachment['meta']['url'];
				}
			}
			return $attachment;
		});
	}


	/**
	 * return tha attachment record of post
	 *
	 * @return     \     ( description_of_the_return_value )
	 */
	public function twig_function_perm()
	{
		return new \Twig_SimpleFunction('perm', function()
		{

			$caller  = null;
			$action  = null;
			$user_id = null;

			if(isset($this->data->login['id']))
			{
				$user_id = $this->data->login['id'];
			}

			$args = func_get_args();

			if(isset($args[0]))
			{
				$caller = $args[0];
			}
			if(isset($args[1]))
			{
				$action = $args[1];
			}
			if(isset($args[2]))
			{
				$user_id = $args[2];
			}
			\lib\permission::$user_id = $user_id;
			return \lib\permission::access($caller, $action);
		});
	}
}
?>