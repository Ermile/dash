<?php
namespace lib\api;
class args_callback
{
	/**
	 * make some methods for use best args usages
	 * @param array $_variables array args for save in this class
	 */
	public function __construct($_variables)
	{
		if(is_array($_variables))
		{
			foreach ($_variables as $key => $value) {
				$this->$key = $value;
			}
		}
	}


	/**
	 * get http method get|post|put|delete
	 * @return string method name
	 */
	public function method()
	{
		return $this->method;
	}


	/**
	 * return match variables with name and if not exists name return null
	 * @param  string $_name match name
	 * @param  array $_args indexes
	 * @return array|string        match name values
	 */
	public function get($_name, ...$_args)
	{
		if(isset($this->match->$_name))
		{
			if(count($_args) == 0)
			{
				return $this->match->$_name;
			}
			elseif(array_key_exists($_args[0], $this->match->$_name))
			{
				return $this->match->$_name[$_args[0]];
			}
			return null;
		}
		else
		{
			return null;
		}
	}


	/**
	 * caller method for get() best use. exp: get_user(1) => get('user', 1)
	 * @param  string $_name match name
	 * @param  array $_args indexes
	 * @return array|string        match name values
	 */
	public function __call($_name, $_args)
	{
		if(preg_match("/^get_(.+)$/", $_name, $method_name))
		{
			array_unshift($_args, $method_name[1]);
			return call_user_func_array([$this, 'get'], $_args);
		}
	}
}
?>