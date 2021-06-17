<?php
namespace dash;

/**
 * Class for application.
 */
class app
{
	private static $REQUEST_APP = [];


	/**
	 * Init request
	 *
	 * @param      <type>  $_args  The arguments
	 */
	public static function variable($_args, $_options = [])
	{
		if(is_array($_args))
		{
			$default =
			[
				'raw_field' => []
			];

			if(!is_array($_options))
			{
				$_options = [];
			}

			$_options = array_merge($default, $_options);

			if(!empty($_options['raw_field']))
			{
				$new_safe = [];
				foreach ($_args as $key => $value)
				{
					if(in_array($key, $_options['raw_field']))
					{
						$new_safe[$key] = \dash\safe::safe($value, 'raw');
					}
					else
					{
						$new_safe[$key] = \dash\safe::safe($value);
					}
				}
				$args = $new_safe;
			}
			else
			{
				$args = \dash\safe::safe($_args);
			}


			self::$REQUEST_APP = $args;
		}
	}


	public static function request_set($_args)
	{
		self::$REQUEST_APP = $_args;
	}


	/**
	 * get request
	 */
	public static function request($_name = null)
	{
		if($_name)
		{
			if(array_key_exists($_name, self::$REQUEST_APP))
			{
				return self::$REQUEST_APP[$_name];
			}

			return null;
		}
		else
		{
			return self::$REQUEST_APP;
		}
	}


	/**
	 * check the request has exist or no
	 *
	 * @param      <type>  $_name  The name
	 */
	public static function isset_request($_name)
	{
		if(array_key_exists($_name, self::$REQUEST_APP))
		{
			return true;
		}
		return false;
	}



	/**
	 * save log
	 */
	public static function log()
	{
		\dash\db\logs::set(...func_get_args());
	}


	/**
	 * Logs a meta.
	 */
	public static function log_meta($_level = null, $_array = [])
	{
		if($_array)
		{
			return $_array;
		}
		else
		{
			return null;
		}

		// $log_meta = null;

		// switch ($_level)
		// {
		// 	// EASY LOG
		// 	case 1:
		// 		$log_meta =
		// 		[
		// 			'data' => null,
		// 			'meta' =>
		// 			[
		// 				'input' => \dash\app::request(),
		// 				'args'  => $_array,
		// 			],
		// 		];
		// 		break;

		// 	// MEDIOM LOG
		// 	case 2:
		// 		$log_meta =
		// 		[
		// 			'data' => null,
		// 			'meta' =>
		// 			[
		// 				'input'   => \dash\app::request(),
		// 				'session' => $_SESSION,
		// 				'args'    => $_array,
		// 			],
		// 		];
		// 		break;

		// 	// HARD LOG
		// 	case 3:
		// 		$log_meta =
		// 		[
		// 			'data' => null,
		// 			'meta' =>
		// 			[
		// 				'request' => $_REQUEST,
		// 				'server'  => $_SERVER,
		// 				'session' => $_SESSION,
		// 				'input'   => \dash\app::request(),
		// 				'args'    => $_array,
		// 			],
		// 		];
		// 		break;

		// 	// not log detail
		// 	default:
		// 		$log_meta = null;

		// 		break;
		// }
		// return $log_meta;
	}


	/**
	 * return the url of static logo file
	 */
	public static function static_logo_url()
	{
		$url = \dash\url::siftal(). '/';
		$url .= 'images/default/logo.png';
		return $url;
	}


	/**
	 * return the url of static logo file
	 */
	public static function static_image_url($_type = 'image')
	{
		$url = \dash\url::siftal(). '/';
		$url .= 'images/default/'. $_type. '.png';
		return $url;
	}


	public static function static_avatar_url($_type = 'default')
	{
		$url = \dash\url::siftal(). '/';
		switch ($_type)
		{
			case 'male':
				$url .= 'images/avatar/man.png';
				break;

			case 'female':
				$url .= 'images/avatar/woman.png';
				break;

			default:
				$url .= 'images/default/avatar.png';
				break;
		}
		return $url;
	}


	public static function ready($_data)
	{
		$result = [];
		foreach ($_data as $key => $value)
		{
			switch ($key)
			{
				case 'id':
				case 'user_id':
				case 'creator':
				case 'parent':
					if(isset($value))
					{
						$result[$key] = \dash\coding::encode($value);
					}
					else
					{
						$result[$key] = null;
					}
					break;

				case 'displayname':
					if(!$value && $value != '0')
					{
						$value = T_("Whitout name");
					}
					$result[$key] = $value;
					break;

				case 'logo':
					if($value)
					{
						$result['logo'] = $value;
					}
					else
					{
						$result['logo'] = \dash\app::static_logo_url();
					}
					break;

				case 'avatar':
					if($value)
					{
						$avatar = $value;
					}
					else
					{
						if(isset($_data['gender']))
						{
							if($_data['gender'] === 'male')
							{
								$avatar = \dash\app::static_avatar_url('male');
							}
							else
							{
								$avatar = \dash\app::static_avatar_url('female');
							}
						}
						else
						{
							$avatar = \dash\app::static_avatar_url();
						}
					}
					$result[$key] = $avatar;
					break;

				default:
					$result[$key] = $value;
					break;
			}
		}

		return $result;
	}


	public static function fix_avatar($_data)
	{
		if(!isset($_data['avatar']))
		{
			$_data['avatar'] = null;
		}

		$result = [];
		foreach ($_data as $key => $value)
		{
			switch ($key)
			{
				case 'avatar':
					if($value)
					{
						$avatar = $value;
					}
					else
					{
						if(isset($_data['gender']))
						{
							if($_data['gender'] === 'male')
							{
								$avatar = \dash\app::static_avatar_url('male');
							}
							else
							{
								$avatar = \dash\app::static_avatar_url('female');
							}
						}
						else
						{
							$avatar = \dash\app::static_avatar_url();
						}
					}
					$result[$key] = $avatar;
					break;

				default:
					$result[$key] = $value;
					break;
			}
		}

		return $result;
	}


	public static function fix_gender($_data)
	{
		if(!isset($_data['gender']))
		{
			$_data['gender'] = null;
		}

		$result = [];

		foreach ($_data as $key => $value)
		{
			switch ($key)
			{
				case 'gender':
					$result[$key] = $value;
					if($value === 'male')
					{
						$result['gender_string'] = T_("Mr");
						$result['genderString']  = T_("Mr");
					}
					elseif($value === 'female')
					{
						$result['gender_string'] = T_("Mrs");
						$result['genderString']  = T_("Mrs");
					}
					else
					{
						$result['gender_string'] = null;
						$result['genderString']  = null;
					}
					break;

				default:
					$result[$key] = $value;
					break;
			}
		}

		return $result;
	}

}
?>