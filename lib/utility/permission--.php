<?php
namespace lib\utility;

/** Access: handle permissions **/
class permission
{
	/**
	 * this library get permissions and check it!
	 * v1.0
	 */

	/**
	 * permission array
	 *
	 * @var        array
	 */
	public static $PERMISSION = [];

	public static $get_from_session = true;

	/**
	 * get permission data from session or from parametr
	 *
	 * @return     <type>  ( description_of_the_return_value )
	 */
	private static function permission_data()
	{
		if(self::$get_from_session)
		{
			return $_SESSION;
		}
		else
		{
			return self::$PERMISSION;
		}
	}


	/**
	 * return
	 * @param  string $_loc  location
	 * @param  string $_type type of permission needed
	 * @return [type]        [description]
	 */
	public static function access($_content = null, $_loc = null, $_type = null, $_block = null)
	{
		$myStatus = null;
		$su       = null;
		// if user is superviser then set su to true
		// permission id 1 is supervisior of system
		if(isset(self::permission_data()['user']['permission']) && self::permission_data()['user']['permission'] === "1")
		{
			$su       = true;
			$suStatus = self::permListFill('su');
		}

		// if programmer not set content, give it automatically from address
		if($_content === 'all')
		{
			$myStatus = [];
			if($su)
			{
				foreach ($suStatus as $key => $value)
				{
					if(isset($value['enable']))
					{
						$myStatus[$key] = $value['enable'];
					}
				}
			}
			elseif(isset(self::permission_data()['permission']))
			{
				foreach (self::permission_data()['permission'] as $key => $value)
				{
					if(isset($value['enable']))
					{
						$myStatus[$key] = $value['enable'];
					}
				}
			}
			return $myStatus;
		}
		elseif(!$_content)
		{
			$_content = \lib\router::get_repository_name();
			if($_content !== "content")
			{
				$_content = substr($_content, strpos($_content, '_') + 1);
			}
		}
		if(!isset($suStatus[$_content]) || !isset($suStatus[$_content]['modules']))
		{
			$su = false;
		}

		// if user want specefic location
		if($_loc == 'all')
		{
			if($su)
			{
				$myStatus = $suStatus[$_content]['modules'];
			}
			elseif(isset(self::permission_data()['permission'][$_content]['modules']))
			{
				$myStatus = self::permission_data()['permission'][$_content]['modules'];
			}
		}
		elseif($_loc)
		{
			if($_type)
			{
				if($su)
				{
					if(isset($suStatus[$_content]['modules'][$_loc][$_type]))
					{
						$myStatus = $suStatus[$_content]['modules'][$_loc][$_type];
					}
				}
				elseif(isset(self::permission_data()['permission'][$_content]['modules'][$_loc][$_type]))
				{
					$myStatus = self::permission_data()['permission'][$_content]['modules'][$_loc][$_type];
				}
			}
			else
			{
				if($su)
				{
					$myStatus = $suStatus[$_content]['modules'][$_loc];
				}
				elseif(isset(self::permission_data()['permission'][$_content]['modules'][$_loc]))
				{
					$myStatus = self::permission_data()['permission'][$_content]['modules'][$_loc];
				}
			}
		}
		// else if not set location and only want enable status
		else
		{
			if($su)
			{
				$myStatus = $suStatus[$_content]['enable'];
			}
			elseif(isset(self::permission_data()['permission'][$_content]['enable']))
			{
				$myStatus = self::permission_data()['permission'][$_content]['enable'];
			}
		}


		if(!$myStatus)
		{
			if($_block === "notify" && $_type && $_loc)
			{
				$msg = null;
				switch ($_type)
				{
					case 'view':
					$msg = "You can't view this part of system";
					break;

					case 'add':
					$msg = T_("You can't add new") .' '. T_($_loc);
					break;

					case 'edit':
					$msg = T_("You can't edit") .' '. T_($_loc);
					break;

					case 'delete':
					$msg = T_("You can't delete") .' '. T_($_loc);
					break;

					default:
					$msg = "You can't access to this part of system";
					break;
				}
				$msg = $msg. "<br/> ". T_("Because of your permission");

				\lib\debug::error(T_($msg));
				// exit();
			}
			elseif($_block)
			{
				\lib\error::access(T_("You can't access to this page!"));
			}
			else
			{
				// do nothing!
			}
		}
		return $myStatus;
	}


	/**
	 * return the modules of each part of system
	 * first check if function declare then return the permissions module of this content
	 * @param  [string] $_content content name
	 * @return [array]  return the permission modules list
	 */
	public static function moduleList($_content)
	{
		$myList      = [];
		$contentName = preg_replace("/content_([^\\\]+)/", "content_" . $_content, get_class(\lib\main::$controller));
		if(get_class(\lib\main::$controller) == $contentName && method_exists($contentName, 'permModules'))
		{
			$myList = $contentName::permModules();
		}
		else
		{
			$manifest_class = new \lib\controller\manifest('content_' . $_content);
			$manifest       = $manifest_class->get();
			$myList = [];
			if(isset($manifest['modules']))
			{
				$myList = $manifest['modules']->modules_search('permissions');
			}

		}
		if(count($myList))
		{

			// recheck return value from permission modules list func
			foreach ($myList as $permLoc => $permValue)
			{
				if(is_array($permValue))
				{
					$permCond = ['view', 'add', 'edit', 'delete', 'admin'];
					$myList[$permLoc] = null;
					foreach ($permCond as $value)
					{
						if(in_array($value, $permValue))
						{
							$myList[$permLoc][$value] = 'show';
							// $myList[$permLoc][$value] = ;
						}
						else
						{
							// $myList[$permLoc][$value] = 'hide';
						}
					}
				}
				else
				{
					$myList[$permLoc] = null;
				}
			}
		}
		// return result
		return $myList;
	}


	/**
	 * [permListFill description]
	 * @param  boolean $_fill [description]
	 * @return [type]         [description]
	 */
	public static function permListFill($_fill = false)
	{
		$permResult = [];
		$permCond   = ['view', 'add', 'edit', 'delete', 'admin'];

		foreach (\lib\utility\option::contentList() as $myContent)
		{
			// for superusers allow access
			if($_fill === "su")
			{
				$permResult[$myContent]['enable'] = true;
			}
			// if request fill for using in model give data from post and fill it
			elseif($_fill)
			{
				// step1: get and fill content enable status
				$postValue = \lib\utility::post('content-'.$myContent);
				if($postValue === 'on')
				{
					$permResult[$myContent]['enable'] = true;
				}
				else
				{
					$permResult[$myContent]['enable'] = false;
				}
			}
			// else fill as null
			else
			{
				$permResult[$myContent]['enable'] = null;
			}

			// step2: fill content modules status
			foreach (self::moduleList($myContent) as $myLoc =>$value)
			{
				foreach ($permCond as $cond)
				{
					// for superusers allow access
					if($_fill === "su")
					{
						$permResult[$myContent]['modules'][$myLoc][$cond] = true;
					}
					// if request fill for using in model give data from post and fill it
					elseif($_fill)
					{
						$locName = $myContent. '-'. $myLoc.'-'. $cond;
						$postValue = \lib\utility::post($locName);
						if($postValue === 'on')
						{
							$permResult[$myContent]['modules'][$myLoc][$cond] = true;
						}
						// else
						// {
							// $permResult[$myContent]['modules'][$myLoc][$cond] = null;
						// }
					}
					else
					{
						$permResult[$myContent]['modules'][$myLoc][$cond] = null;
					}
				}
			}
		}
		return $permResult;
	}
}
?>