<?php
namespace lib\mvc\models;

trait template
{
	/**
	 * this fuction check the url entered from user in database
	 * first search in posts and if not exist search in terms table
	 * @return [array] datarow of result if exist else return false
	 */
	function s_template_finder($_args = null)
	{
		// first of all search in url field if exist return row data
		// $tmp_result = $this->get_posts(true, $_args);
		// if($tmp_result)
		// {
		// 	return $tmp_result;
		// }

		// // if url not exist in posts then search in terms table and if exist return row data
		// $tmp_result = $this->get_terms(true, $_args);
		// if($tmp_result)
		// {
		// 	return $tmp_result;
		// }

		// // if url not exist in terms table then analyze the url to load similar
		// // tag/(.*) || cat/(.*)
		// // this url get from term type
		// $tmp_result = $this->get_terms_type(true, $_args);
		// if($tmp_result)
		// {
		// 	return $tmp_result;
		// }
		// else retun false
		return false;
	}


	/**
	 * [get_posts description]
	 * @param  boolean $_forcheck [description]
	 * @return [type]             [description]
	 */
	public function get_posts($_forcheck = false, $_args = null, $_options = [])
	{
		$default_options =
		[
			'check_language' => true,
			'type'      => null,
			'check_status'   => true,
			'status'    => [],
		];

		if(!is_array($_options))
		{
			$_options = [];
		}

		$_options = array_merge($default_options, $_options);

		$type = null;

		if(isset($_options['type']))
		{
			if(is_string($_options['type']))
			{
				$type = " posts.type = '$_options[type]' AND ";
			}
			elseif(is_array($_options['type']))
			{
				$temp_type = implode("','", $_options['type']);
				$type      = " posts.type IN ('$temp_type') AND ";
			}
		}

		// check shortURL
		$shortURL = \lib\db\url::checkShortURL();
		if($shortURL & is_array($shortURL))
		{
			// set datarow
			$datarow = $shortURL;
		}
		else
		{
			$url = $this->url('path');
			$url = str_replace("'", '', $url);
			$url = str_replace('"', '', $url);
			$url = str_replace('`', '', $url);
			$url = str_replace('%', '', $url);

			if(substr($url, 0, 7) == 'static/' || substr($url, 0, 6) == 'files/')
			{
				return false;
			}

			$language = \lib\define::get_language();
			$preview  = \lib\utility::get('preview');

			$check_language = null;

			if($_options['check_language'])
			{
				$check_language =
				"
					(
						posts.language IS NULL OR
						posts.language = '$language'
					) AND
				";
			}

			$qry =
			"
				SELECT
					*
				FROM
					posts
				WHERE
				$check_language
				$type
				posts.url = '$url'
				LIMIT 1
			";

			$datarow = \lib\db::get($qry, null, true);

			if(isset($datarow['user_id']) && (int) $datarow['user_id'] === (int) $this->login('id'))
			{
				// no problem to load this post
			}
			else
			{
				if($preview)
				{
					// no problem to load this post
				}
				else
				{
					if(isset($datarow['status']) && $datarow['status'] == 'publish')
					{
						// no problem to load this poll
					}
					else
					{
						if($_options['check_status'])
						{
							if(isset($datarow['status']) && in_array($datarow['status'], $_options['status']))
							{
								// no problem to load this poll
							}
							else
							{
								$datarow = false;
							}
						}
						else
						{
							// no problem to load this poll
						}
					}
				}
			}

			// we have more than one record
			if(isset($datarow[0]))
			{
				$datarow = false;
			}

		}

		if(isset($datarow['id']))
		{
			$id = $datarow['id'];
		}
		else
		{
			$datarow = false;
			$id  = 0;
		}

		if($datarow && $id)
		{


			if($_forcheck && isset($datarow['type']) && isset($datarow['slug']))
			{
				// get cat from url until last slash
				$cat = substr($datarow['url'], 0, strrpos($datarow['url'], '/'));
				// if type of post exist in cat, remove it
				if($datarow['type'] === substr($cat, 0, strlen($datarow['type'])))
				{
					$cat = substr($cat, strlen($datarow['type'])+1);
				}

				$return =
				[
					'table' => 'posts',
					'type' => $datarow['type'],
					'cat'  => $cat,
					'slug' => $datarow['slug'],
				];
				return $return;
			}
			else
			{
				foreach ($datarow as $key => $value)
				{
					// if field contain json, decode it
					if(substr($value, 0, 1) == '{')
					{
						$datarow[$key] = json_decode($value, true);
						if(is_null($datarow[$key]) && preg_match("/meta$/", $key))
						{
							$datarow[$key] = json_decode(html_entity_decode($value), true);
						}
					}
				}

				// get meta of this post
				// $meta = \lib\db\posts::get_meta($id);
				// $datarow['postmeta'] = $meta;
				return $datarow;
			}
		}
		return false;
	}


	/**
	 * Gets the terms type.
	 * by url
	 *
	 * @param      boolean  $_forcheck  The forcheck
	 * @param      <type>   $_args      The arguments
	 */
	public function get_terms_type($_forcheck = false, $_args = null)
	{
		$url = $this->url('path');
		$url = str_replace("'", '', $url);
		$url = str_replace('"', '', $url);
		$url = str_replace('`', '', $url);
		$url = str_replace('%', '', $url);

		$split_url  = preg_split("/\//", $url);
		$started_by = null;
		if(isset($split_url[0]))
		{
			$started_by = $split_url[0];
		}

		$search     = null;
		$lenght     = 10;
		$lenght_max = 50;

		if(\lib\utility::get("q") != '')
		{
			$search = \lib\utility::get("q");
		}

		$get_lenght = \lib\utility::get("lenght");
		if($get_lenght != '')
		{
			if(intval($get_lenght) < $lenght_max)
			{
				$lenght = $get_lenght;
			}
		}
		$term_type = null;
		switch ($started_by)
		{
			case 'tag':
			case 'cat':
				$term_type = $started_by;
				break;

			default:
				return false;
				break;
		}

		if($_forcheck)
		{
			return
			[
				'table' => 'terms',
				'type' => $term_type,
				'slug' => $term_type,
			];
		}
		else
		{
			$datarow = \lib\db\terms::search($search, ['term_type' => $term_type, 'end_limit' => $lenght]);
			return $datarow;
		}
	}
}
?>
