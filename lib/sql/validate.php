<?php
namespace lib\sql;
trait validate{
	public static $validate_error = array(
		'int' => array(
			1246 => 'Out of range value',
			1366 => 'Incorrect integer value'
			)
		);
	function auto_validate($name, $field, $value){
		$type = $field->type;
		preg_match("/^([^@]*)@(.*)$/", $type, $tp);
		if(isset($tp[1])){
			if(method_exists($this, 'auto_validate_'.$tp[1])){
				return $this->{"auto_validate_".$tp[1]}($value, $tp);
			}
		}
		return true;
	}

	function auto_validate_tinyint($value, $tp){
		return $this->auto_validate_fn_int($value, $tp);
	}

	function auto_validate_int($value, $tp){
		return $this->auto_validate_fn_int($value, $tp);

	}

	function auto_validate_fn_int($value, $tp){
		$len = $tp[2];
		preg_match("/_([^_]+)$/", debug_backtrace()[1]['function'], $name);
		switch ($name[1]) {
			case 'tinyint':
				$max = 127;
				$min = -128;
				break;
			case 'smallint':
				$max = 32767;
				$min = -32768;
				break;
			case 'mediumint':
				$max = 8388607;
				$min = -8388608;
				break;
			case 'int':
				$max = 2147483647;
				$min = -2147483648;
				break;
			case 'bigint':
				$max = 9223372036854775807;
				$min = -9223372036854775808;
				break;
		}
		if(!preg_match("/^\-?\d*$/", $value)){
			return $this::$validate_error['int'][1366];
		}elseif(!preg_match("/^\-?\d{0,$len}$/", $value)){
			return $this::$validate_error['int'][1246];
		}elseif($min > $value || $max < $value){
			return $this::$validate_error['int'][1246];
		}
		return true;
	}

}
?>