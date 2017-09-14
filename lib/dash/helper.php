<?php
namespace lib\dash;
/**
 * autoload dash core helper
 */
class helper
{
	public function __call($name, $args)
	{
		$class_name = "helper\\{$name}";
		if(!class_exists($class_name)){
			\error::page("helper $name not found");
		}else{
			return new $class_name($args);
		}
	}
}