<?php
namespace lib\dash;
/**
 * autoload dash core lib
 */
class lib
{
	public $prefix;
	public $static = false;
	public function __construct($_args = null, $_static = false){
		$this->static = $_static;
		$this->prefix = $_args ? "\\". trim($_args[0], "\\"). "\\" : "\\";
	}
	public function __call($name, $args){
		$path = array("ilib", "lib");
		foreach ($path as $key => $value) {
			$class_name = "{$value}{$this->prefix}{$name}";
			if(class_exists($class_name)){
				if($this->static === true)
				{
					return $class_name;
				}
				return new $class_name(...$args);
			}
		}
		\lib\error::core("lib\\{$name}");
	}
}