<?php
namespace lib;
/**
 * Class for debug.
 */
class debug
{
	/**
	 * static var
	 *
	 * @var        array
	 */
	private static $error    = array();
	private static $warn     = array();
	private static $true     = array();
	private static $msg      = array();
	private static $property = array();
	private static $form     = array();
	private static $check    = true;
	private static $result   = null;
	private static $title;

	/**
	 * STATUS
	 * 0 => error
	 * 1 => true
	 * 2 => warn
	 *
	 * @var        array
	 */
	public static  $status   = 1;


	/**
	 * create error message (fatal)
	 * @param  [type]  $_error   [description]
	 * @param  boolean $_element [description]
	 * @param  string  $_group   [description]
	 * @return [type]            [description]
	 */
	private static function static_error($_error, $_element = false, $_group = 'public')
	{
		self::$check = false;
		self::$status = 0;
		array_push(self::$error, array('title' => $_error, "element" => $_element, "group" => $_group));
	}


	/**
	 * create warn message
	 * @param  [type]  $_error   [description]
	 * @param  boolean $_element [description]
	 * @param  string  $_group   [description]
	 * @return [type]            [description]
	 */
	private static function static_warn($_error, $_element = false, $_group = 'public')
	{
		if(self::$check)
		{
			self::$status = 2;
		}
		array_push(self::$warn,	array('title' => $_error, "element" => $_element, "group" => $_group));
	}


	/**
	 * create true message (successful)
	 * @param  [type]  $_error   [description]
	 * @param  boolean $_element [description]
	 * @param  string  $_group   [description]
	 * @return [type]            [description]
	 */
	private static function static_true($_error, $_element = false, $_group = 'public')
	{
		array_push(self::$true,	array('title' => $_error, "element" => $_element, "group" => $_group));
	}


	/**
	 * { function_description }
	 *
	 * @param      <type>  $_title  The title
	 */
	private static function static_title($_title)
	{
		self::$title = $_title;
	}


	/**
	 * set msg for showing data with ajax on pages
	 * @param  [string or array] $_name  if array we seperate it in many msg else it's name of msg
	 * @param  [string or array] $_value if pass
	 * @param  [bool]            $_reset
	 * @return set global value
	 */
	private static function static_msg($_name, $_value = null, $_reset = null)
	{
		if($_reset)
		{
			self::$msg = array();
		}

		if(is_array($_name))
		{
			foreach($_name as $key => $value)
			{
				self::$msg[$key] = $value;
			}
		}
		else
		{
			if($_value !== false && $_value !== null)
			{
				self::$msg[$_name] = $_value;
			}
			else
			{
				array_push(self::$msg, $_name);
			}
		}
	}


	/**
	 * set property for debug
	 * @param  [type]  $_property [description]
	 * @param  boolean $_value    [description]
	 * @return [type]             [description]
	 */
	private static function static_property($_property, $_value = false)
	{
		if(is_array($_property))
		{
			foreach ($_property as $key => $value)
			{
				self::$property[$key] = $value;
			}
		}
		else
		{
			if($_value !== false)
			{
				self::$property[$_property] = $_value;
			}
			else
			{
				array_push(self::$property, $_property);
			}
		}
	}

	/**
	 * set result
	 *
	 * @param      array  $_result  The result
	 */
	private static function static_result($_result)
	{
		self::$result = $_result;
	}


	/**
	 * set form of messages
	 * @param  [type] $_form [description]
	 * @return [type]        [description]
	 */
	private static function static_form($_form)
	{
		if(!array_search($_form, self::$form))
		{
			self::$form[] = $_form;
		}
	}


	/**
	 * compile message and return it for show in page
	 * @param  boolean $_json convert return value to json or not
	 * @return [string]       depending on condition return json or string
	 */
	private static function static_compile($_json = false)
	{
		$debug           = array();
		$debug['status'] = self::$status;
		$debug['title']  = self::$title;
		$messages        = array();
		if(count(self::$error) > 0) $messages['error'] = self::$error;
		if(count(self::$warn) > 0)  $messages['warn']  = self::$warn;
		if(count(self::$msg) > 0)   $debug['msg']      = self::$msg;
		if(count(self::$property) > 0)
		{
			foreach (self::$property as $key => $value)
			{
				$debug[$key] = $value;
			}
		}
		if(self::$result !== null && self::$result !== false)
		{
			$debug['result'] = self::$result;
		}

		if(count(self::$form) > 0) $debug['msg']['form'] = self::$form;
		if(count(self::$true) > 0 || count($debug) == 0) $messages['true'] = self::$true;
		if(count($messages) > 0) $debug['messages'] = $messages;
		return ($_json)? json_encode($debug) : $debug;
	}


	/**
	 * get items
	 *
	 * @param      <type>  $_property  The property
	 * @param      <type>  $_args      The arguments
	 *
	 * @return     <type>  ( description_of_the_return_value )
	 */
	private static function get($_property, $_args = null)
	{
		$return = [];
		if(isset(self::${$_property}))
		{
			$return = self::${$_property};
		}

		if(is_null($_args))
		{
			return $return;
		}
		elseif(isset($return[$_args]))
		{
			return $return[$_args];
		}
		return null;
	}


	/**
	 * { function_description }
	 *
	 * @param      <type>  $_func_name  The function name
	 * @param      <type>  $_args       The arguments
	 */
	public static function __callStatic($_func_name, $_args)
	{
		$func_name = "static_".$_func_name;
		if(substr($_func_name, 0,4) == 'get_')
		{
			return self::get(substr($_func_name, 4), ...$_args);
		}
		elseif(method_exists(get_class(), $func_name))
		{
			return self::$func_name(...$_args);
		}
		else
		{
			\lib\error::internal("function ". __CLASS__. "::$_func_name() not exist");
		}
	}


	/**
	 * { function_description }
	 *
	 * @param      <type>  $_status  The status
	 *
	 * @return     <type>  ( description_of_the_return_value )
	 */
	public static function db_return($_status)
	{
		$return = new \lib\db\db_return();
		return $return->set_ok($_status);
	}

	/**
	 *******************************************************************
	 * dynamic mode
	 *
	 *******************************************************************
	 */

	/**
	 * dynamic var
	 *
	 * @var        array
	 */
	private $dynamic_error    = array();
	private $dynamic_warn     = array();
	private $dynamic_true     = array();
	private $dynamic_msg      = array();
	private $dynamic_property = array();
	private $dynamic_form     = array();
	private $dynamic_title;
	private $dynamic_check    = true;
	private $dynamic_result   = null;

	/**
	 * STATUS
	 * 0 => error
	 * 1 => true
	 * 2 => warn
	 *
	 * @var        array
	 */
	public  $dynamic_status   = 1;


	/**
	 * { function_description }
	 *
	 * @param      <type>  $_func_name  The function name
	 * @param      <type>  $_args       The arguments
	 */
	public function __call($_func_name, $_args)
	{
		$func_name = "dynamic_".$_func_name;

		if(preg_match("/^get_(.*)$/", $_func_name, $peroperty))
		{
			return $this->get($peroperty[1], ...$_args);
		}
		elseif(preg_match("/^is_(.*)$/", $_func_name, $peroperty))
		{
			return $this->is($peroperty[1], ...$_args);
		}
		elseif(method_exists($this, $func_name))
		{
			return $this->$func_name(...$_args);
		}
		else
		{
			\lib\error::internal("function ". __CLASS__. "->$_func_name() not exist");
		}
	}


	/**
	 * create error message (fatal)
	 * @param  [type]  $_error   [description]
	 * @param  boolean $_element [description]
	 * @param  string  $_group   [description]
	 * @return [type]            [description]
	 */
	private function dynamic_error($_error, $_element = false, $_group = 'public')
	{
		$this->dynamic_check = false;
		$this->dynamic_status = 0;
		array_push($this->dynamic_error, array('title' => $_error, "element" => $_element, "group" => $_group));
		return $this;
	}


	/**
	 * create warn message
	 * @param  [type]  $_error   [description]
	 * @param  boolean $_element [description]
	 * @param  string  $_group   [description]
	 * @return [type]            [description]
	 */
	private function dynamic_warn($_error, $_element = false, $_group = 'public')
	{
		if($this->dynamic_check)
		{
			$this->dynamic_status = 2;
		}
		array_push($this->dynamic_warn,	array('title' => $_error, "element" => $_element, "group" => $_group));
		return $this;
	}


	/**
	 * create true message (successful)
	 * @param  [type]  $_error   [description]
	 * @param  boolean $_element [description]
	 * @param  string  $_group   [description]
	 * @return [type]            [description]
	 */
	private function dynamic_true($_error, $_element = false, $_group = 'public')
	{
		array_push($this->dynamic_true,	array('title' => $_error, "element" => $_element, "group" => $_group));
		return $this;
	}


	/**
	 * { function_description }
	 *
	 * @param      <type>  $_title  The title
	 */
	private function dynamic_title($_title)
	{
		$this->dynamic_title = $_title;
		return $this;
	}


	/**
	 * set msg for showing data with ajax on pages
	 * @param  [string or array] $_name  if array we seperate it in many msg else it's name of msg
	 * @param  [string or array] $_value if pass
	 * @param  [bool]            $_reset
	 * @return set global value
	 */
	private function dynamic_msg($_name, $_value = null, $_reset = null)
	{
		if($_reset)
		{
			$this->dynamic_msg = array();
		}

		if(is_array($_name))
		{
			foreach($_name as $key => $value)
			{
				$this->dynamic_msg[$key] = $value;
			}
		}
		else
		{
			if($_value)
			{
				$this->dynamic_msg[$_name] = $_value;
			}
			else
			{
				array_push($this->dynamic_msg, $_name);
			}
		}
		return $this;
	}


	/**
	 * set property for debug
	 * @param  [type]  $_property [description]
	 * @param  boolean $_value    [description]
	 * @return [type]             [description]
	 */
	private function dynamic_property($_property, $_value = false)
	{
		if(is_array($_property))
		{
			foreach ($_property as $key => $value)
			{
				$this->dynamic_property[$key] = $value;
			}
		}
		else
		{
			if($_value !== false)
			{
				$this->dynamic_property[$_property] = $_value;
			}
			else
			{
				array_push($this->dynamic_property, $_property);
			}
		}
		return $this;
	}


	/**
	 * set result
	 *
	 * @param      array   $_result  The result
	 *
	 * @return     <type>  ( description_of_the_return_value )
	 */
	private function dynamic_result($_result)
	{
		if(!is_array($_result))
		{
			$_result = [$_result];
		}
		$this->dynamic_result = $_result;

		return $this;
	}


	/**
	 * set form of messages
	 * @param  [type] $_form [description]
	 * @return [type]        [description]
	 */
	private function dynamic_form($_form)
	{
		if(!array_search($_form, $this->dynamic_form))
		{
			$this->dynamic_form[] = $_form;
		}
		return $this;
	}


	/**
	 * compile message and return it for show in page
	 * @param  boolean $_json convert return value to json or not
	 * @return [string]       depending on condition return json or string
	 */
	private function dynamic_compile($_json = false)
	{
		$debug = array();
		$debug['status'] = $this->dynamic_status;
		$debug['title']  = $this->dynamic_title;
		$messages = array();
		if(count($this->dynamic_error) > 0) $messages['error'] = $this->dynamic_error;
		if(count($this->dynamic_warn) > 0)  $messages['warn']  = $this->dynamic_warn;
		if(count($this->dynamic_msg) > 0)   $debug['msg']      = $this->dynamic_msg;
		if(count($this->dynamic_property) > 0)
		{
			foreach ($this->dynamic_property as $key => $value)
			{
				$debug[$key] = $value;
			}
		}
		if(is_array($this->dynamic_result))
		{
			$debug['result'] = $this->dynamic_result;
		}

		if(count($this->dynamic_form) > 0) $debug['msg']['form'] = $this->dynamic_form;
		if(count($this->dynamic_true) > 0 || count($debug)       == 0) $messages['true'] = $this->dynamic_true;
		if(count($messages) > 0) $debug['messages']       = $messages;
		return ($_json)? json_encode($debug) : $debug;
	}



	public function is($_name, ...$_args)
	{
		if(!isset($_args[0]))
		{
			$_args[0] = true;
		}
		return $this->get($_name) == $_args[0];
	}
}
?>