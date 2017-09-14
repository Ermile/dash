<?php
namespace lib\api;

class config
{
	public $api, $api_method, $model_api_name, $view_api_name;
	public $REST, $SERVER;

	public function __construct($api, $api_method, $model_api_name, $view_api_name)
	{
		$this->api = $api;
		$this->api_method = $api_method;
		$this->model_api_name = $model_api_name;
		$this->view_api_name = $view_api_name;
	}


	public function REST($route = null)
	{
		if($route === null){
			$this->REST = $this->SERVER;
		}else{
			$this->REST = func_get_args();
		}
		$this->REST = !is_array($this->REST) ? array('') : $this->REST;
		if(\lib\temp::get('api') && $this->api_method == mb_strtolower($_SERVER['REQUEST_METHOD'])){
			$this->check(...$this->REST);
		}
		return $this;
	}


	public function SERVER($route = null)
	{
		if($route === null){
			$this->SERVER = $this->REST;
		}else{
			$this->SERVER = func_get_args();
		}
		$this->SERVER = !is_array($this->SERVER) ? array('') : $this->SERVER;
		if(!\lib\temp::get('api')) {
			if(
				(preg_match("/^post|put$/", $this->api_method) && $_SERVER['REQUEST_METHOD'] == "POST") ||
				(preg_match("/^get|delete$/", $this->api_method) && $_SERVER['REQUEST_METHOD'] == "GET") ||
				($this->api_method == mb_strtolower($_SERVER['REQUEST_METHOD']))
			)
			{
				$this->check(...$this->SERVER);

			}
		}
		return $this;
	}


	public function ALL($route = null)
	{
		if($route === null)
		{
			$this_url =  '';

			if(\lib\router::get_class() != 'home')
			{
				$this_url =  \lib\router::get_class();
			}

			if(\lib\router::get_method() != 'home')
			{
				$this_url .= '/'. \lib\router::get_method();
			}

			$route = array($this_url);
		}else{
			$route = func_get_args();
		}
		$this->REST(...$route)->SERVER();
		return $this;

	}


	public function check($route)
	{
		if ($this->api->controller->method) return;
		$route_callback = call_user_func_array(array($this->api->controller, 'route'), func_get_args());
		$api_callback = null;
		if($route_callback->status)
		{
			$this->api->controller->method = $this->api_method;
			$args_object = new args_callback(array(
				'method' => $this->api_method,
				'match' => $route_callback->match
				));
			if($this->model_api_name)
			{
				$model_api_name = "api_".$this->model_api_name;

				$this->api->controller->model_api_processor = $object = object(array("method" => $model_api_name, "args" => $args_object));
			}
			if($this->view_api_name && !\lib\temp::get('api'))
			{
				$view_api_name = "view_".$this->view_api_name;
				if($this->model_api_name)
				{
					$args_object->api_callback = $api_callback;
				}
				$this->api->controller->view_api_processor = $object = object(array("method" => $view_api_name, "args" => $args_object));
			}
		}
	}

}
?>