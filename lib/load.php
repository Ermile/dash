<?php
namespace lib;

class load
{
	private $class;
	public function __construct($controller, $class)
	{
		$this->controller = $controller;
		$this->class = $class;
	}


	public function method($controller_root, $method)
	{
		$controller = $this->controller;
		$class = $this->class;
		$controller_model_name = $controller_root.'\\'.$class;
		$func = new \ReflectionMethod($controller_model_name, $method);

		$Closure = $func->getClosure(new $controller_model_name);
		$controller->$class()->Methods[$method] = $Closure->bindTo($controller->$class(), $controller->$class());
		return $controller->$class()->Methods[$method];
	}
}
?>