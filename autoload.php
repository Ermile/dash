<?php
/**
 * require default define
 */
require_once ("define.php");


/*
**	In object-oriented applications one of the biggest annoyances is having to write a long list of needed includes
**	at the beginning of each script.
**	an __autoload() function automatically called in case you are trying to use a class/interface which hasn't been defined yet.
**	By calling this function the scripting engine is given a last chance to load the class before PHP fails with an error.
*/
class autoload
{
	static $require     = array();
	static $core_prefix = array('lib', 'ilib', 'cls', 'database', 'mvc', 'addons');
	static $autoload    = false;

	/**
	 * [load description]
	 * @param  [type] $name [description]
	 * @return [type]       [description]
	 */
	static function load($name)
	{
		if(isset(self::$require[$name]))
		{
			return;
		}

		$split_name = preg_split("[\\\]", $name);
		if(count($split_name) > 1)
		{
			$file_addr = self::iget_file_name($split_name);
			if($file_addr !== false)
			{
				self::$require[$name] = 1;
				$file_addr = stream_resolve_include_path($file_addr);
				include_once($file_addr);
			}
			else
			{
				$name = preg_replace("/[\\\]/", DIRECTORY_SEPARATOR, $name).'.php';
				$file_addr = stream_resolve_include_path($name);
				if($file_addr)
				{
					self::$require[$name] = 1;
					include_once($file_addr);
				}
				else
				{
					return false;
				}
			}
		}
	}

	/**
	 * [get_file_name description]
	 * @param  [type] $split_name [description]
	 * @return [type]             [description]
	 */
	static function get_file_name($split_name)
	{
		list($prefix, $sub_path, $exec_file) = self::ifile_splice($split_name);
		$prefix_file = null;
		if (preg_grep("/^$prefix$/", self::$core_prefix))
		{
			$file_addr = self::icheck_file($prefix, $sub_path, $exec_file);
			return $file_addr;
		}

		$prefix_file = \lib\router::get_repository();
		$prefix_file = preg_replace("#\/[^\/]+\/?$#", '', $prefix_file);
		$file_addr   = $prefix_file. '/'. $prefix.'/'. $sub_path. $exec_file;
		if(!file_exists($file_addr))
		{
			$file_addr = false;
		}
		return $file_addr;
	}

	/**
	 * [check_file description]
	 * @param  [type] $prefix    [description]
	 * @param  [type] $sub_path  [description]
	 * @param  [type] $exec_file [description]
	 * @return [type]            [description]
	 */
	static function check_file($prefix, $sub_path, $exec_file)
	{
		if(!defined($prefix))
		{
			return false;
		}
		$prefix_file = constant($prefix);
		$file_addr   = $prefix_file .$sub_path .$exec_file;
		if(stream_resolve_include_path($file_addr))
		{
			return $file_addr;
		}
		return false;
	}

	/**
	 * [file_splice description]
	 * @param  [type] $split_name [description]
	 * @return [type]             [description]
	 */
	static function file_splice($split_name)
	{
		$prefix = $split_name[0];
		array_shift($split_name);

		$exec_file = end($split_name);
		array_pop($split_name);

		$sub_path = (count($split_name) > 0) ? join($split_name, "/") .'/' : '';

		return array($prefix, $sub_path, $exec_file .".php");
	}

	static function __callStatic($_name, $_args){
		$name = preg_replace("/^i/", "", $_name);
		if(method_exists("iautoload", $name))
		{
			return iautoload::{$name}(...$_args);
		}
		else
			return autoload::{$name}(...$_args);
	}
}

if(stream_resolve_include_path('iautoload.php'))
{
	require_once ("iautoload.php");
}
// register autoload
if(class_exists('iautoload'))
{
	spl_autoload_register("\iautoload::load");
}
else
{
	spl_autoload_register("\autoload::load");
}


/**
 * define new dash class
 */
class dash extends \lib\dash
{

}
// create a new instance from dash
new dash;
?>