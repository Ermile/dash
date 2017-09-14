<?php
namespace lib\router;
class route
{
	public $status = true;
	public $match;
	public $route_rule = array();

	public function __construct($run = true)
	{
		if($run){
			call_user_func_array(array($this,'check_route'), func_get_args());
		}
	}


	public function check_route()
	{
		$this->match = object();
		$args = func_get_args();
		if(count($args) == 0) return;
		$route = $args[0];
		$fn = isset($args[1])? $args[1] : false;
		if(is_string($route))
		{
			$this->route_rule['url'] = $route;
			$this->url($route);
		}
		else
		{
			if(!isset($route['max']) && isset($route['url']))
			{
				if(is_string($route['url'])){
					$route['max'] = 1;
				}elseif(is_array($route['url'])){
					$route['max'] = count($route['url']);
				}elseif(is_string($route['url']) && preg_match("/^(\/.*\/|#.*#|[.*])[gui]{0,3}$/i", $route['url'])){
					$route['max'] = 0;
				}
			}
			if(isset($route['max'])){
				$_max = $route['max'];
				unset($route['max']);
				$route['max'] = $_max;
			}else{
				$route['max'] = 0;
			}
			$this->route_rule = $route;
			foreach ($route as $key => $value)
			{
				if(method_exists($this, $key))
				{
					$this->$key($this->route_rule[$key]);
				}
			}
		}
		if($this->status === true && is_object($fn))
		{
			$arg = array_splice($args, 2);
			array_push($arg, $this->match);
			call_user_func_array($fn, $arg);
		}
		return $this->status;
	}


	function max($max)
	{
		$url = \lib\router::get_url(-1);
		if(count($url) > 0 && \lib\router::get_class() == $url[0]){
			array_shift($url);
		}
		if(count($url) > 0 && \lib\router::get_method() == $url[0]){
			array_shift($url);
		}
		if(count($url) > $max){
			$this->status = false;
		}
	}


	function min($min)
	{
		if(count(\lib\router::$url_array) < $min){
			$this->status = false;
		}
	}


	function fn($function)
	{
		if(is_object($function)){
			$status = call_user_func($function);
		}elseif(is_array($function)){
			$status = call_user_func_array($function[0], array_splice($function, 1));
		}else{
			$status = false;
		}
		$this->status = (!$status)? false : $this->status;
	}

	function real_url($url_Parameters){
		$this->parametersCaller('real_url', $url_Parameters, \lib\router::$real_url_array, '/');
	}

	function property($url_Parameters){
		$this->parametersCaller('property', $url_Parameters, \lib\router::$url_index_property);
	}

	function url($url_Parameters){
		$this->parametersCaller('url', $url_Parameters, \lib\router::$url_array);
	}

	function sub_domain($sub_domain_Parameters){
		$this->parametersCaller('sub_domain', $sub_domain_Parameters, \lib\router::$sub_domain, '.');
	}

	function domain($domain_Parameters){
		$this->parametersCaller('domain', $domain_Parameters, \lib\router::$domain, '.');
	}

	function get($get){
		$this->parametersCaller('get', $get, $_GET, '&');
	}

	function post($post){
		$this->parametersCaller('post', $post, $_POST, '&');
	}

	function parametersCaller($name, $parameters, $array, $join = "/")
	{
		if(!is_array($parameters)){
			$match = $this->check_parameters($parameters, join($array, $join));
			if($match !== false){
				if(!isset($this->match->$name)) $this->match->$name = array();
				array_push($this->match->$name, $match);
			}
			return;
		}
		foreach ($parameters as $key => $_value) {
			$value = $_value;
			if(!isset($array[$key])){
				if(is_array($_value) && count($_value) > 1 && $_value[1] === true){
					continue;
				}else{
					$this->status = false;
					break;
				}
			}elseif(is_array($_value)){
				$value = $_value[0];
			}
			$match = $this->check_parameters($value, $array[$key]);
			if($match){
				if(is_array($_value)){
					$this->route_rule['max'] = array_key_exists('max', $this->route_rule) ? $this->route_rule['max'] + 1 : 1;
				}
				if(count($_value) > 2){
					$this->match->{$_value[2]} = $match;
				}
				if(!isset($this->match->$name)) $this->match->$name = array();
				array_push($this->match->$name, $match);
			}
		}
	}

	function check_parameters($reg, $value)
	{
		if(preg_match("/^(\/.*\/|#.*#|[.*])[gui]{0,3}$/i", $reg)){
			if(!preg_match($reg, $value, $array)){
				$this->status = false;
			}else{
				return $array;
			}
		}elseif($reg != $value){
			$this->status = false;
		}else{
			return $value;
		}
		return false;
	}
}
?>