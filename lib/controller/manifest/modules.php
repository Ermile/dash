<?php
namespace lib\controller\manifest;

class modules{
	public $modules_class;
	public function __construct($_value, $_modules_object){
		$this->modules_class = $_modules_object ? $_modules_object : new \lib\controller\modules();
		foreach ($_value as $key => $value) {
			$this->save($key, $value);
		}
	}

	public function save($_key, $_value){
		if(array_key_exists('_mod', $_value)){
			$mod = $_value['_mod'];
			unset($_value['_mod']);
		}else{
			$mod = 'update';
		}
		switch ($mod) {
			case 'new':
				$this->modules_class->add_modules($_key, $_value);
				break;

			case 'delete':
			break;

			default:
				$this->modules_class->edit_modules($_key, $_value);
			break;
		}
	}

	public function get(){
		return $this->modules_class;
	}
}
?>