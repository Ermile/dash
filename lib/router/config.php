<?php
namespace lib\router;
trait config
{
	//protocol
	static public $protocol           = "http";
	static public $base               = null;
	static public $prefix_base        = null;
	static public $sub_is_fake        = null;
	static public $sub_real            = null;

	// original routers
	static public $class, $method;

	// real urls
	static public $real_url_string    = array();
	static public $real_url_array     = array();

	// dynamic url
	static public $url_string         = array();
	static public $url_array          = array();

	// url property exp: add          =20/name=hasan => add = 20; name = hasan
	static public $url_index_property = array();

	// url array exp: track/add/name  =hasan => 0 = track; 1 = name; 2 = 'name=hasan'
	static public $url_array_property = array();

	// domain array
	static public $domain             = array();
	static public $sub_domain         = array();

	// repository
	static $repository                = null;

	// repository
	public static $repository_name    = 'content';
	public static $repository_finded  = null;

	// api status, if you want to disable it, change this value
	static $auto_api                  = true;

	// api status, if you want to disable it, change this value
	static $auto_repository           = true;

	// controller runing
	static $controller;

	// cache array all static property
	public static $cache_array = array();

	public static $storage = array();

	public static function set_storage($name, $value)
	{
		self::$storage[$name] = $value;
	}

	public static function get_storage($name)
	{
		return isset(self::$storage[$name]) ? self::$storage[$name] : null;
	}

	public static function cache_config()
	{
		$class = new \ReflectionClass('\lib\router');
		$arr = $class->getStaticProperties();
		self::$cache_array = $arr;
	}

	public static function get_class_static($naem)
	{
		return (isset(self::$cache) && self::$cache) ? self::$cache_array[$naem] : \lib\router::${$naem};

	}

	public static function set_class_static($name, $value)
	{
		if(isset(self::$cache) &&  self::$cache)
		{
			self::$cache_array[$name] = $value;
		}
		else
		{
			\lib\router::${$name} = $value;
		}

	}
	/**
	 * PROTOCOL
	 */

	public static function get_protocol()
	{
		return self::get_class_static('protocol');
	}

	public static function set_protocol($protocol)
	{
		return self::set_class_static('protocol', $protocol);
	}

	/**
	 * URL PROPERTY
	 */

	public static function add_url_property($value)
	{
		$array = self::get_class_static('url_array_property');
		array_unshift($array, $value);
		self::set_class_static('url_array_property', $array);
		self::check_url_router();
	}


	public static function check_url_router()
	{
		foreach (self::$url_array_property as $key => $value)
		{
			$index_property = preg_split("[=]", $value);
			self::$url_index_property[$index_property[0]] = (isset($index_property[1])) ? $index_property[1] : null;
		}
	}

	/**
	 * MAIN URL
	 */

	public static function unshift_url($value)
	{
		$array = self::get_class_static('url_array');
		array_unshift($array, $value);
		$string = join($array, '/');
		self::set_class_static('url_string', $string);
		self::set_class_static('url_array', preg_split("[/]", $string));
	}

	public static function push_url($value)
	{
		$array = self::get_class_static('url_array');
		array_push($array, $value);
		$string = join($array, '/');
		self::set_class_static('url_string', $string);
		self::set_class_static('url_array', preg_split("[/]", $string));
	}


	/**
	 * [remove_url description]
	 * @param  [type] $value [description]
	 * @return [type]        [description]
	 */
	public static function remove_url($value)
	{
		$array = self::get_class_static('url_array');
		$index = array_search($value, $array);
		if($index !== -1)
		{
			unset($array[$index]);
			$string = join($array, '/');
			if(empty($string))
			{
				self::set_class_static('url_string', null);
				self::set_class_static('url_array', array());
			}
			else
			{
				self::set_class_static('url_string', $string);
				self::set_class_static('url_array', preg_split("[/]", $string));
			}
		}
	}

	public static function remove_url_property($_search)
	{
		$value = self::get_url_property($_search);
		if($value !== null)
		{
			self::remove_url("$_search=$value");
		}
	}

	public static function shift_url()
	{
		$array = self::get_class_static('url_array');
		array_shift($array);
		$string = join($array, '/');
		self::set_class_static('url_string', $string);
		self::set_class_static('url_array', preg_split("[/]", $string));
	}

	public static function pop_url($value)
	{
		$array = self::get_class_static('url_array');
		array_pop($array, $value);
		$string = join($array, '/');
		self::set_class_static('url_string', $string);
		self::set_class_static('url_array', preg_split("[/]", $string));
	}

	public static function set_url($value = '')
	{
		$string = is_array($value) ? join($array, '/') : $value;
		$array = is_array($value) ? $value : explode("/", $value);
		self::set_class_static('url_string', $string);
		self::set_class_static('url_array', $array);
	}

	/**
	 * GETTER
	 */

	public static function get_property($search = null, $index, $property = false, $splitor = '/')
	{
		// if user pass nothing or null then splite address array with slash /
		if($search === null)
		{
			return join($index, $splitor);
		}
		elseif($search === -1)
		{
			return $index;
		}

		// if user pass undeline '_' or space ' ' as search use it as splitor
		elseif($search === '_' || $search === ' ')
		{
			$tmp_result = join($index, $search);
			// find position of assign if exist in array
			$tmp_strpos = strpos($tmp_result,'=');
			// then splite from start until assign character
			if($tmp_strpos!==false)
			{
				$tmp_result = substr($tmp_result, 0, $tmp_strpos);
			}

			if(!$tmp_result && $search === '_')
			{
				if(self::$repository_name === 'content')
				{
					return 'homepage';
				}
				else
				{
					return 'home';
				}
			}

			return $tmp_result;
		}

		elseif(preg_match("/^\d+$/", $search))
		{
			if(isset($index[$search]))
			{
				return $index[$search];
			}
			else
			{
				return null;
			}
		}
		elseif($property !== false)
		{
			if(isset($property[$search]))
			{
				return $property[$search];
			}
			else
			{
				return null;
			}
		}

		return null;
	}

	public static function get_url_property($search = null)
	{
		return self::get_property($search, self::get_class_static('url_array_property'), self::get_class_static('url_index_property'));
	}

	public static function get_url($search = null)
	{
		return self::get_property($search, self::get_class_static('url_array'));

	}

	public static function get_real_url($search = null)
	{
		return self::get_property($search, self::get_class_static('real_url_array'));
	}

	public static function get_root_domain($_arg = null)
	{
		$myvalue   = null;
		$mydomain = self::get_domain(-1);
		// if another domain language is not exist then set com
		if($_arg=='tld')
		{
			return $mydomain[count($mydomain)-1];
		}
		// if arg has a any value return domain name without tld
		elseif($_arg && count($mydomain)-2 >= 0)
		{
			return $mydomain[count($mydomain)-2];
		}

		// if user don't pass a parameter and count of current domain allow set domain name
		if(count($mydomain) > 1)
		{
			$myvalue = $mydomain[count($mydomain)-2].'.';
		}

		// add com for sample.com to myvalue name
		$myvalue .= $mydomain[count($mydomain)-1];

		return $myvalue;
	}


	public static function get_real_domain()
	{
		$domain = self::get_domain(-1);
		$result = $domain;
		$prefix_base = self::get_class_static('prefix_base');
		if(count($domain) === 3)
		{
			$sub = $domain[0];
			// remove subdomain
			array_shift($domain);
			// add subdomain as part of url
			array_push($domain, $sub);
			$result = $domain[0].'.'. $domain[1].'/'. ($prefix_base ? $prefix_base .'/' : ''). $domain[2];
		}
		else
		{
			$result = self::get_domain() . ($prefix_base ? '/'. $prefix_base : '');
		}

		return $result;
	}

	public static function get_domain($search = null)
	{
		return self::get_property($search, self::get_class_static('domain'), false, '.');
	}

	public static function set_domain($domain = null)
	{
		if(!$domain)
		{
			$domain = self::get_root_domain();
		}
		self::set_class_static('domain', is_array($domain) ? $domain : explode('.', $domain));
	}

	/**
	 * this funstion give and set SubDomain
	 * @param [type] $_subdomain subdomain in array or string
	 */
	public static function set_sub_domain($_subdomain)
	{
		if(is_array($_subdomain))
		{
			$_subdomain = implode('.', $_subdomain);
		}

		$mydomain = self::get_domain(-1);
		array_unshift($mydomain, $_subdomain);

		self::set_domain($mydomain);

		if(!is_array($_subdomain))
		{
			$_subdomain = explode('.', $_subdomain);
		}

		self::set_class_static('sub_domain', $_subdomain);
	}

	public static function get_sub_domain($search = null)
	{
		return self::get_property($search, self::get_class_static('sub_domain'), false, '.');
	}


	public static function get_class()
	{
		return self::get_class_static('class');
	}

	public static function get_method()
	{
		return self::get_class_static('method');
	}

	public static function set_class($class)
	{
		// if(self::$method !== 'home')
		// {
		// 	self::add_url_property(self::$method);
		// }
		// if(self::$class !== 'home')
		// {
		// 	self::add_url_property(self::$class);
		// }
		return self::set_class_static('class', $class);
	}

	public static function set_method($method)
	{
		// if(self::$method !== 'home'){
		// 	self::add_url_property(self::$method);
		// }
		return self::set_class_static('method', $method);
	}

	public static function set_repository($repository, $use_root_dir = true)
	{
		$repository = ($use_root_dir)? root.$repository : $repository;

		$repository = (preg_match("/\/$/", $repository)) ? $repository : $repository.'/';
		self::$repository = rtrim($repository, '/');
		$addr = explode("/", self::$repository);
		self::set_repository_name(end($addr));
	}

	public static function set_repository_hasan($repository, $dont_use_root_dir = false)
	{
		$repository = ($dont_use_root_dir)? $dont_use_root_dir.$repository : root.$repository;

		$repository = (preg_match("/\/$/", $repository)) ? $repository : $repository.'/';
		self::$repository = '/'.trim($repository, '/').'/';
		$addr = explode("/", self::$repository);
		self::set_repository_name(end($addr));
	}

	public static function get_repository()
	{
		return self::$repository;
	}

	public static function set_repository_name($name)
	{
		// if(!defined($name)){
		// 	define($name, '/'.self::get_repository().'/');
		// }
		self::$repository_name = $name;
	}

	public static function get_repository_name()
	{
		return self::$repository_name;
	}

	public static function set_controller($controller)
	{
		self::$controller = $controller;
	}

	public static function get_controller()
	{
		return self::$controller;
	}



	/*
	 * Below line added by javad for future use *************************************
	 */
	public static function urlParser($_url = 'current', $_type = 'domain', $_arg = null)
	{
		// if dont pass url use referrer url
		if($_url === 'referer')
		{
			$_url = isset($_SERVER['HTTP_REFERER'])? $_SERVER['HTTP_REFERER']: null;
			// if dont set shema for url set it, usually it occur on local
			$_url = substr($_url, 0, 4) === 'http'? $_url: Protocol.'://'.$_url;
		}


		switch ($_type)
		{
			case 'sub':
				$myplace = isset($myplace)? $myplace: 3;
			case 'domain':
				$myplace = isset($myplace)? $myplace: 2;
			case 'tld':
				$myplace = isset($myplace)? $myplace: 1;

			case 'host':
				$myurl   = parse_url($_url, PHP_URL_HOST);
				if($_type === 'host')
				{
					return $myurl;
				}

				if (isset($myurl) && $myurl)
				{
					$myurl   = explode('.', $myurl);
					if(count($myurl)>=$myplace)
					{
						$myurl   = $myurl[count($myurl)-$myplace];
					}
					else
					{
						$myurl = false;
					}
				}
				else
					$myurl = null;
				break;

			// retun the path of url
			case 'path':
				if($_url === 'current')
				{
					$_url = isset($_SERVER['REQUEST_URI'])?  $_SERVER['REQUEST_URI']:  null;
				}

				// clear url
				$_url  = self::urlfilterer($_url);
				$myurl = parse_url($_url, PHP_URL_PATH);
				if($_arg === 'array')
				{
					$myurl = trim($myurl, '/');
					$myurl = explode('/', $myurl);
				}
				return $myurl;
				break;

			// return full url of referrer
			case 'full':
				$myurl = $_url;
				break;

			default:
				$myurl = null;
				break;
		}

		return $myurl;

		// add other mode like below to complete this function

		// PHP_URL_SCHEME
		// PHP_URL_PORT
		// PHP_URL_USER
		// PHP_URL_PASS
		// PHP_URL_PATH	-
		// PHP_URL_QUERY
		// PHP_URL_FRAGMENT
	}

	// sanitize url
	public static function urlfilterer($_input, $_strip = true)
	{
		$_input = urldecode($_input);
		$_input = str_ireplace(array("\0", '%00', "\x0a", '%0a', "\x1a", '%1a'), '', $_input);
		if ($_strip)
			$_input = strip_tags($_input);

		$_input = htmlentities($_input, ENT_QUOTES, 'UTF-8'); // or whatever encoding you use...
		return trim($_input);
	}

	/**
	 * Function to get the client IP address
	 * @return [type] [description]
	 */
	public static function get_clientIP($_change = true)
  	{
		$ipaddress = null;
		if (isset($_SERVER["HTTP_CF_CONNECTING_IP"]))
		{
			$ipaddress = $_SERVER["HTTP_CF_CONNECTING_IP"];
			$_SERVER['REMOTE_ADDR'] = $_SERVER["HTTP_CF_CONNECTING_IP"];
		}
		elseif (isset($_SERVER['HTTP_CLIENT_IP']))
		{
			$ipaddress = $_SERVER['HTTP_CLIENT_IP'];
		}
		elseif(isset($_SERVER['HTTP_X_FORWARDED_FOR']))
		{
			$ipaddress = $_SERVER['HTTP_X_FORWARDED_FOR'];
		}
		elseif(isset($_SERVER['HTTP_X_FORWARDED']))
		{
			$ipaddress = $_SERVER['HTTP_X_FORWARDED'];
		}
		elseif(isset($_SERVER['HTTP_FORWARDED_FOR']))
		{
			$ipaddress = $_SERVER['HTTP_FORWARDED_FOR'];
		}
		elseif(isset($_SERVER['HTTP_FORWARDED']))
		{
			$ipaddress = $_SERVER['HTTP_FORWARDED'];
		}
		elseif(isset($_SERVER['REMOTE_ADDR']))
		{
			$ipaddress = $_SERVER['REMOTE_ADDR'];
		}
		else
		{
			$ipaddress = null;
		}

		if($_change)
		{
			// sprintf will then write it as an unsigned integer.
			$ipaddress = sprintf("%u",ip2long( $ipaddress ));
			// $ipaddress = ip2long( $ipaddress );
		}

		return $ipaddress;
	}
}
?>