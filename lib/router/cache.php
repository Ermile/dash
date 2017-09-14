<?php
namespace lib\router;

class cache
{
	use config;
	public static $cache = true;
	public $_self;

	public function __construct()
	{
		$this->cache_config();
		if(method_exists($this, '_construct')) call_user_func_array(array($this, '_construct'), func_get_args());
	}


	public static function get_cache()
	{
		return self::$cache_array;
	}


	public static function set_cache($name, $value)
	{
		self::$cache_array[$name] = $value;
	}
}
?>