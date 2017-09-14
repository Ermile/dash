<?php
namespace lib;
class object{
	public function __call($name, $args){
		if(preg_match("/^(push|shift|pop|unshift)(\_(.*))?$/", $name, $aName)){
			if(isset($aName[3])){
				if(!is_array($this->{$aName[3]}))
					$this->{$aName[3]} = (array) $this->{$aName[3]};
				$oName = $aName[3];
				$value = isset($args[0])? $args[0] : null;
			}elseif(isset($args[0])){
				if(!is_array($this->{$args[0]}))
					$this->{$args[0]} = (array) $this->{$args[0]};
				$oName = $args[0];
				$value = isset($args[1])? $args[1] : null;
			}
			switch ($aName[1]) {
				case 'push':
				array_push($this->$oName, $value);
				break;

				case 'unshift':
				array_unshift($this->$oName, $value);
				break;

				case 'shift':
				array_shift($this->$oName);
				break;

				case 'pop':
				array_pop($this->$oName);
				break;
			}
		}else{
			$this->$name = $args;
		}
		return $this;
	}
	public function __toArray(){
		return (array) $this;
	}
}
?>