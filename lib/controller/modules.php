<?php
namespace lib\controller;
class modules{
	public $modules_list = array();

	public function get_modules($name = null, $attr = null){
		if(!$name){
			$arr_sort = array();
			$arr_unsort = array();
			foreach ($this->modules_list as $key => $value) {
				if(array_key_exists('order', $value) && is_int($value['order']))
					$arr_sort[$key] = $value;
				else
					$arr_unsort[$key] = $value;
			}
			return array_merge($arr_sort, $arr_unsort);
		}elseif(array_key_exists($name, $this->modules_list)){
			if(!$attr){
				return $this->modules_list[$name];
			}else{
				return array_key_exists($attr, $this->modules_list[$name]) ? $this->modules_list[$name][$attr] : false;
			}
		}else{
			return null;
		}
	}

	public function edit_modules($_name, $_attr){
		if(!array_key_exists($_name, $this->modules_list)){
			$this->add_modules($_name, $_attr);
		}else{
			$this->modules_list[$_name] = array_merge($this->modules_list[$_name], $_attr);
		}
	}

	public function add_modules($_name, $_attr = array()){
		$_attr['name'] = $_name;
		$_attr['title'] = (isset($_attr['title'])) ? $_attr['title'] : T_(ucfirst($_name));
		if(array_key_exists("parent", $_attr)
			&& array_key_exists($_attr['parent'], $this->modules_list)
			&& array_key_exists('disable', $this->modules_list[$_attr['parent']])
			&& $this->modules_list[$_attr['parent']]['disable'] == true
			)
		{
			$_attr['disable'] = true;
		}
		if(array_key_exists('addons', $_attr))
		{
			$_attr['addons'] = $this->add_addons($_attr['addons']);
		}
		$this->modules_list[$_name] = $_attr;
	}

	public function modules_search($attr){
		$arr = array();
		foreach ($this->get_modules() as $key => $value) {
			if(array_key_exists($attr, $value)){
				$arr[$key] = $value[$attr];
			}
		}
		return $arr;
	}

	public function modules_hasnot($attr){
		$arr = array();
		foreach ($this->get_modules() as $key => $value) {
			if(!array_key_exists($attr, $value)){
				$arr[$key] = $value;
			}
		}
		return $arr;
	}

	private function add_addons($_addons)
	{
		foreach ($_addons as $key => $value)
		{
			if(is_int($key))
			{
				unset($_addons[$key]);
				$_addons[$value] = true;
			}
		}
		return $_addons;
	}
}
?>