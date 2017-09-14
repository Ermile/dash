<?php
namespace lib\utility;

/** Option: handle options of project from db **/
class option
{
	/**
	 * this library get options from db only one times!
	 * v2.3
	 */

	// declare private static variable to save options
	private static $options;

	/**
	 * get options from db and return the result
	 * @param  [type]  $_key  [description]
	 * @param  string  $_type [description]
	 * @param  boolean $_meta [description]
	 * @return [type]         [description]
	 */
	public static function get($_key = null, $_type = 'value', $_meta = false)
	{
		// fetch records from database
		if(!self::$options)
		{
			self::$options = self::fetch();
		}

		$result  = [];

		// check condition for show best result
		if($_key === true)
		{
			$result = self::$options;
		}
		elseif($_key && isset(self::$options[$_key]))
		{
			if($_type && !($_type === true))
			{
				if(isset(self::$options[$_key][$_type]))
				{
					if($_meta)
					{
						if(isset(self::$options[$_key][$_type][$_meta]))
						{
							$result = self::$options[$_key][$_type][$_meta];
						}
						else
						{
							$result = null;
						}
					}
					else
					{
						$result = self::$options[$_key][$_type];
					}
				}
				else
					$result = null;
			}
			else
			{
				$result = self::$options[$_key];
			}
		}
		else
		{
			$result = null;
		}
		// var_dump($result);
		return $result;
	}


	/**
	 * fetch options from db then fix and return result
	 * @param  boolean $_pemissionDetails [description]
	 * @return [type]                     [description]
	 */
	public static function fetch()
	{
		// connect to default database
		\lib\db::connect(true);

		// set query string
		$qry =
		"SELECT `options`.*
			FROM `options`
			WHERE user_id IS NULL AND
				post_id IS NULL AND
				(
					options.cat like 'option%' OR
					options.cat like 'permissions'
				)";

		// run query and give result
		$result = @mysqli_query(\lib\db::$link, $qry);
		// if result is not mysqli result return false
		if(!is_a($result, 'mysqli_result'))
		{
			// no record exist
			return false;
		}

		// fetch all records
		$result     = \lib\db::fetch_all($result);
		$permList   = [];
		$qry_result = null;


		foreach ($result as $key => $row)
		{
			// save permissions to query result
			if($row['cat'] == 'permissions')
			{
				// if status is enable
				if($row['status'] == 'enable')
				{
					$qry_result['permissions']['meta'][$row['key']]         = json_decode($row['meta'], true);
					$qry_result['permissions']['meta'][$row['key']]['id']   = $row['key'];
					$qry_result['permissions']['meta'][$row['key']]['name'] = $row['value'];


					// save current user permission as option permission value
					if(isset($_SESSION['user']['permission']) && $row['key'] == $_SESSION['user']['permission'])
					{
						$qry_result['permissions']['value'] = $row['key'];
					}
				}
			}
			else
			{
				$myValue  = $row['value'];
				$myMeta   = $row['meta'];
				$myStatus = $row['status'];
				if($myStatus === 'enable' || $myStatus === 'on' || $myStatus === 'active')
				{
					$myStatus = true;
				}
				else
				{
					$myStatus = false;
				}

				if(substr($myValue, 0,1) == '{')
				{
					$myValue = json_decode($myValue, true);
				}

				if(substr($myMeta, 0,1) == '{')
				{
					$myMeta = json_decode($myMeta, true);
				}

				// save result
				$qry_result[$row['key']] =
				[
					'value'  => $myValue,
					'meta'   => $myMeta,
					'status' => $myStatus
				];
			}

		}

		return $qry_result;
	}


	/**
	 * return permission detail of requested
	 * work with permission id or name
	 * @param  [type] $_id if empty return current user permission
	 * @return [type]      array contain permission detail
	 */
	public static function permission($_id = null)
	{
		$permission = [];
		// use current user permission if isset
		if(!$_id && isset($_SESSION['user']['permission']))
		{
			$_id = $_SESSION['user']['permission'];
		}
		// if user pass string of permission name search with name
		if(!is_numeric($_id))
		{
			$permission = self::permList();
			$_id        = array_search($_id, $permission);
		}
		// search in permisssions and get detail of it
		$permission = self::get('permissions', 'meta', $_id);
		// return result
		return $permission;
	}


	/**
	 * return the list of permission
	 * key is id of permission
	 * value is the name of permission
	 * @return [type] [description]
	 */
	public static function permList($_flip = false)
	{
		$permList = self::get('permissions', 'meta');
		if(!$permList)
			$permList = [];
		$permList = array_column($permList, 'name', 'id');
		if($_flip)
		{
			$permList = array_flip($permList);
		}

		return $permList;
	}


	/**
	 * return the list of contents exist in current project and addons
	 * @return [type] [description]
	 */
	public static function contentList($_addMain = false)
	{
		// get all content exist in core and current project
		$addons   = glob(addons. "content_*", GLOB_ONLYDIR);
		$project  = glob(root. "content_*",   GLOB_ONLYDIR);
		$contents = array_merge($addons, $project);
		$myList   = [];

		foreach ($contents as $myContent)
		{
			$myContent = preg_replace("[\\\\]", "/", $myContent);
			$myContent = substr( $myContent, ( strrpos( $myContent, "/" ) + 1) );
			$myContent = substr( $myContent, ( strrpos( $myContent, "_" ) + 1) );
			array_push($myList, $myContent);
		}
		if($_addMain)
		{
			array_push($myList, 'home');
			$myList = array_reverse($myList);
		}
		$myList = array_flip($myList);
		unset($myList['account']);
		$myList = array_flip($myList);

		return $myList;
	}


	/**
	 * return list of languages in current project
	 * read form folders exist in includes/languages
	 * @return [type] [description]
	 */
	public static function languages($_dir = false)
	{
		// detect languages exist in current project
		$langList = glob(dir_includes.'languages/*', GLOB_ONLYDIR);
		$myList   = ['en' => 'English'];
		foreach ($langList as $myLang)
		{
			$myLang     = preg_replace("[\\\\]", "/", $myLang);
			$myLang     = substr( $myLang, (strrpos($myLang, "/" )+ 1));
			$myLang     = substr($myLang, 0, 2);
			$myLangName = $myLang;
			$myLangDir  = 'ltr';
			switch (substr($myLang, 0, 2))
			{
				case 'fa':
					$myLangName = 'Persian - فارسی';
					$myLangDir  = 'rtl';
					break;

				case 'ar':
					$myLangName = 'Arabic - العربية';
					$myLangDir  = 'rtl';
					break;

				case 'en':
					$myLangName = 'English';
					$myLangDir  = 'ltr';
					break;

				case 'de':
					$myLangName = 'Deutsch';
					break;


				case 'fr':
					$myLangName = 'French';
					break;
			}
			$myList[$myLang] = $myLangName;
		}

		if($_dir)
		{
			return $myLangDir;

		}
		return $myList;
	}


	/**
	 * set new record in options
	 * @param [array] $_args contain key and value of new record
	 */
	public static function set($_args, $_ifExistUpdate = true)
	{
		$datarow =
		[
			'status' => 'enable',
		];

		// add option user if set
		if(isset($_args['user']))
		{
			$op_user = $_args['user'];
			if($op_user === true)
			{
				$op_user = \lib\utility\visitor::user_id(false);
				if(!$op_user)
				{
					$op_user = 'NULL';
				}
			}
			if($op_user)
			{
				$datarow['user_id'] = $op_user;
			}
		}

		// add option post if set
		if(isset($_args['post']))
		{
			$datarow['post_id'] = $_args['post'];
		}

		// add option cat if set
		if(isset($_args['cat']))
		{
			$datarow['cat'] = $_args['cat'];
		}
		else
		{
			return false;
		}

		// add option key if set
		if(isset($_args['key']))
		{
			// replace _USER_ with user_id if exist
			$replace = "";
			if(isset($datarow['user_id']))
			{
				$replace = $datarow['user_id'];
			}

			$_args['key']          = str_replace('_USER_', $replace, $_args['key']);
			$datarow['key'] = $_args['key'];
		}
		else
		{
			return false;
		}

		// add option value if set
		if(isset($_args['value']))
		{
			$datarow['value'] = $_args['value'];
		}
		else
		{
			$datarow['value'] = null;
		}

		// add option meta if set
		if(isset($_args['meta']))
		{
			$datarow['meta'] = $_args['meta'];
			if(is_array($datarow['meta']))
			{
				$datarow['meta'] = json_encode($datarow['meta'], JSON_UNESCAPED_UNICODE);
			}
		}

		// add option status if set
		if(isset($_args['status']))
		{
			// only allow defined$_args['status'])e
			switch ($_args['status'])
			{
				case 'enable':
				case 'disable':
				case 'expire':
					break;

				default:
					$_args['status'] = 'enable';
					break;
			}
			$datarow['status'] = $_args['status'];
		}

		// add date modified manually
		if(isset($_args['modify']) && $_args['modify'] === 'now')
		{
			$datarow['datemodified'] = 'now()';
		}

		// create query string
		$qry_fields = implode(', ', array_keys($datarow));
		foreach ($datarow as $key => $value)
		{
			switch ($key)
			{
				case 'user_id':
				case 'post_id':
				case 'datemodified':
					$datarow[$key] = $value;
					break;

				case 'meta':
					if($value === '++')
					{
						$datarow[$key] = "coalesce($key, 0)". '+1';
					}
					else
					{
						$datarow[$key] = "'". $value. "'";
					}
					break;

				default:
					$datarow[$key] = "'". $value. "'";
					break;
			}
		}
		$qry_values = implode(', ', $datarow);
		// connect to database

		if($_ifExistUpdate)
		{
			// start creating query data
			$qry_data = null;
			foreach ($datarow as $key => $value)
			{
				$qry_data .= $key .'='. $datarow[$key] .', ';
			}
			// remove last ,
			$qry_data = substr($qry_data, 0, -2);
			if(isset($_args['id']) && is_numeric($_args['id']))
			{
				$qry = "UPDATE options SET $qry_data WHERE `id` = ". $_args['id'];
				// var_dump($qry);
			}
			else
			{
				$qry = "UPDATE options
					SET $qry_data
					WHERE
						`cat`   =". $datarow['cat']." AND
						`key`   =". $datarow['key']." AND
						`value` =". $datarow['value'];
			}


			$result = \lib\db::query($qry);
			// if row is match then return true
			// this means row is same and data is duplicate or not
			// affecting row is not important in this condition
			if($result && \lib\db::qry_info('Rows matched'))
			{
				return true;
			}
		}

		// create query string
		$qry = "INSERT INTO options ( $qry_fields ) VALUES ( $qry_values );";
		// execute query
		$result = \lib\db::query($qry);
		// give last insert id
		$last_id = @mysqli_insert_id(\lib\db::$link);
		// if have last insert it return it
		if($last_id)
		{
			return $last_id;
		}
		// return default value
		return false;
	}
}
?>