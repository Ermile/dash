<?php
namespace lib;

class model
{
	use mvc;
	// query lists
	public $querys;

	// sql object
	public $sql;

	public $commit = array();
	public $rollback = array();

	public $transaction = true;
	public function __construct($object = false){
		if(!$object) return;
		$this->querys = object();
		$this->controller = $object->controller;
		if(method_exists($this, '_construct')){
			$this->_construct();
		}
	}

	public function _processor($options = false)
	{
		if($this->transaction && debug::$status)
		{
			if(isset($this->sql))
				$this->sql->commit();
			if(count($this->commit))
				call_user_func_array($this->commit[0], array_slice($this->commit, 1));
		}
		elseif($this->transaction && !debug::$status)
		{
			if(isset($this->sql))
				$this->sql->rollback();
			if(count($this->rollback))
				call_user_func_array($this->rollback[0], array_slice($this->rollback, 1));
		}
		$this->controller->_processor($options);
	}

	public final function commit(){
		$this->commit = func_get_args();
	}

	public final function rollback(){
		$this->rollback = func_get_args();
	}

	public function validate(){
		if(!isset($this->validate)) $this->validate = new \lib\validator\pack;
		return $this->validate;
	}

	public function sql($name = null){
		if(!$this->sql){
			$this->sql = new sql\maker;
			if($this->transaction) $this->sql->transaction();
		}
		$name = $name ? $name : count((array)$this->querys);
		$query = $this->querys->$name = $this->sql;
		return $query;
	}

	public function __get($name){
		if(property_exists($this->controller, $name)){
			return $this->controller->$name;
		}
	}

	public function _call_corridor($name, $args){
		preg_match("/^api_(.+)$/", $name, $spilt_name);
		return count($spilt_name) ? $spilt_name : false;
	}

	public function _call($name, $args, $parm){
		$method = $args[0]->method;
		$api_name = "{$method}_$parm[1]";
		$match = null;
		if(isset($args[0]->match)){
			$match = $args[0]->match;
		}
		return $this->$api_name($args[0], $match);
	}
}
?>