<?php
namespace lib;

trait mvc
{
	public $Methods = array();

	public function inject($_name, $_args)
	{
		preg_match("/^((before|after)_)?(.+)$/", $_name, $event);
		$name = $event[3];
		$event = empty($event[2]) ? 'edit' : $event[2];
		$closure = $_args[0];
		if(!array_key_exists($name, $this->Methods))
		{
			$this->Methods[$name] = array();
		}
		if(!array_key_exists($event, $this->Methods[$name]))
		{
			$this->Methods[$name][$event] = array();
		}
		$bound = @$closure->bindTo($this);
		if($bound)
		{
			array_push($this->Methods[$name][$event], $bound);
		}
	}


	public function __call($_name, $_args)
	{
		$black = array("_construct", "corridor", "config");

		if(method_exists($this, '_call_corridor') && method_exists($this, '_call') && $value = $this->_call_corridor($_name, $_args))
		{
			return $this->_call($_name, $_args, $value);
		}

		elseif(isset($this->Methods[$_name]))
		{
			return $this->mvc_inject_finder($_name, $_args, $_name);
		}
		elseif(preg_match("#^inject_((after_|before_)?.+)$#Ui", $_name, $inject)){
			return $this->inject($inject[1], $_args);
		}
		elseif(preg_match("#^i(.*)$#Ui", $_name, $icall))
		{
			return $this->mvc_inject_finder($_name, $_args, $icall[1]);
		}
		elseif(method_exists($this->controller, $_name) && !preg_grep("/^{$_name}$/", $black))
		{
			return call_user_func_array(array($this->controller, $_name), $_args);
		}

		\lib\error::internal(get_called_class()."()->$_name()");
	}

	public function mvc_inject_finder($_name, $_args, $_call)
	{
		$return = false;
		$method_exists = array_key_exists($_call, $this->Methods);
		$call_method_exists = method_exists($this, $_call);
		if(!$method_exists && !$call_method_exists)
		{
			\lib\error::internal(get_called_class()."()->$_name()");
		}
		if($method_exists && array_key_exists('before', $this->Methods[$_call]))
		{
			foreach ($this->Methods[$_call]['before'] as $key => $before_method)
			{
				if($before_method)
				{
					$before_method(...$_args);
				}
			}
		}

		if($method_exists && array_key_exists('edit', $this->Methods[$_call]))
		{
			$edit_method = end($this->Methods[$_call]['edit']);
			$return = $edit_method(...$_args);
		}
		else
		{
			$return = call_user_func_array(array($this, $_call), $_args);
		}

		if($method_exists && array_key_exists('after', $this->Methods[$_call]))
		{
			foreach ($this->Methods[$_call]['after'] as $key => $after_method)
			{
				if($after_method)
				{
					$after_method(...$_args);
				}
			}
		}
		return $return;
	}

	function addons($_controller = null)
	{
		$controller = $_controller ? $_controller : $this;
		if(!array_key_exists('modules', $controller::$manifest))
		{
			return false;
		}
		$manifest = $controller::$manifest['modules']->get_modules(router::get_class());
		if(!is_array($manifest) || !array_key_exists('addons', $manifest))
		{
			return false;
		}
		$addons = $manifest['addons'];
		foreach ($addons as $key => $value) {
			$this->addons_method_import($key, $controller::$manifest['addons'][$key]);
			if(method_exists($this, 'addons_config') || array_key_exists('addons_config', $this->Methods))
			{
				$this->iaddons_config($key, $controller::$manifest['addons'][$key]);
			}
		}
	}


	function addons_method_import($_name, $_addons)
	{
		$class_type = explode('\\', get_class());
		$class_type = end($class_type);
		$addons_path = $_addons['path'];
		$addons_name = trim($addons_path, '/');
		$addons_name = '\\'.preg_replace("/\//", '\\', $addons_name).'\\'.$class_type;
		if(class_exists($addons_name))
		{
			$addons_class = new $addons_name($this);
			$get_methods_name = new \ReflectionClass($addons_name);
			$methods_name = $get_methods_name->getMethods();
			foreach ($methods_name as $key => $value)
			{
				$Closure = $value->getClosure($addons_class);
				$this->inject($value->name, [$Closure]);
			}
			return true;
		}
		return false;
	}

	public function method_exists($_name)
	{
		if(method_exists($this, $_name) || array_key_exists($_name, $this->Methods))
		{
			return true;
		}
		return false;
	}
}
?>