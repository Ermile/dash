<?php
namespace lib;
/**
 * checking and routing http request and server model. change your mind change your mvc
 */
class api
{
	public $controller;
	public function __construct($controller_class){
		$this->controller = $controller_class;
	}

	public function __call($_name, $_args)
	{
		$api_config = \dash::lib('api')->config($this, $_name, $_args[0], $_args[1]);
		return $api_config;
	}
}
?>