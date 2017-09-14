<?php
namespace lib\validator;

class maker
{
	private $validtorFunctions;
	static $ExtendClass;

	function __construct()
	{
		$this->form = array();
		$this->sql = array();
	}


	function getFunctions()
	{
		return $this->validtorFunctions;
	}


	function __call($name, $arg)
	{

		if(preg_match("/^(form|sql)([A-Z].*)$/", $name, $txterr))
		{
			$method = $txterr[1];
			$this->{$method}[mb_strtolower($txterr[2])] = $arg[0];
			return $this;
		}
		if(!is_object($this->validtorFunctions))
		{
			$this->validtorFunctions = array();
		}

		if(!self::$ExtendClass) self::$ExtendClass = array();
		if(file_exists(lib."utility/validate/$name.php") && !isset(self::$ExtendClass[$name]))
		{
			self::$ExtendClass[$name] = require_once(lib."utility/validate/$name.php");
		}

		if(isset(self::$ExtendClass[$name]) && is_object(self::$ExtendClass[$name]))
		{
			$closure = self::$ExtendClass[$name];
			array_unshift($arg, $closure);
			$this->validtorFunctions[$name] = (count($arg) > 0) ? $arg : true;
		}
		$this->validtorFunctions[$name] = (count($arg) > 0) ? $arg : true;
		$this->form[$name] = T_("error in field").' '. T_($name);
		return $this;
	}
}
?>